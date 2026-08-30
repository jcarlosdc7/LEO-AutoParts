<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Models\SaleReturn;
use App\Support\Decimal;
use App\Support\Money;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileMoney extends Command
{
    protected $signature = 'money:reconcile {--sale= : Reconcile one sale ID}';

    protected $description = 'Detect monetary drift without modifying financial history';

    public function handle(): int
    {
        $failures = [];
        $floatingColumns = DB::table('information_schema.COLUMNS')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->whereIn('DATA_TYPE', ['float', 'double', 'real'])
            ->where(function ($query): void {
                $query->where('COLUMN_NAME', 'regexp', 'price|cost|amount|total|subtotal|balance|tax|discount|difference');
            })
            ->count();
        if ($floatingColumns > 0) {
            $failures[] = "Schema contains {$floatingColumns} floating-point monetary column(s).";
        }

        $query = Sale::query()->with(['saleDetails', 'salePayments', 'paymentMethod', 'cashMovements']);
        if ($this->option('sale')) {
            $query->whereKey((int) $this->option('sale'));
        }
        $query->orderBy('id')->chunkById(200, function ($sales) use (&$failures): void {
            foreach ($sales as $sale) {
                $detailTotal = Money::zero();
                foreach ($sale->saleDetails as $detail) {
                    $expectedLine = Money::fromUnitPrice((string) $detail->price, (int) $detail->quantity);
                    if ($expectedLine->amount() !== (string) $detail->total) {
                        $failures[] = "Sale {$sale->id} detail {$detail->id} total drift.";
                    }
                    $detailTotal = $detailTotal->add($expectedLine);
                }
                if ($detailTotal->amount() !== (string) $sale->total) {
                    $failures[] = "Sale {$sale->id} header total drift.";
                }

                $paymentTotal = $sale->salePayments->reduce(
                    fn (string $total, $payment): string => Decimal::add($total, (string) $payment->amount),
                    '0.00',
                );
                if ($sale->salePayments->isNotEmpty() && $paymentTotal !== (string) $sale->total) {
                    $failures[] = "Sale {$sale->id} payment total drift.";
                }

                if ($sale->paymentMethod?->affects_cash_drawer) {
                    $cashSale = $sale->cashMovements->where('type', 'sale')->reduce(
                        fn (string $total, $movement): string => Decimal::add($total, (string) $movement->amount),
                        Decimal::zero(),
                    );
                    if ($cashSale !== (string) $sale->total) {
                        $failures[] = "Sale {$sale->id} cash movement drift.";
                    }
                }
            }
        });

        SaleReturn::query()
            ->with(['items', 'refund', 'creditNote'])
            ->orderBy('id')
            ->chunkById(200, function ($returns) use (&$failures): void {
                foreach ($returns as $return) {
                    $items = $return->items->reduce(
                        fn (string $total, $item): string => Decimal::add($total, (string) $item->refund_amount),
                        '0.00',
                    );
                    if ($return->refund && $items !== (string) $return->refund->amount) {
                        $failures[] = "Return {$return->id} refund drift.";
                    }
                    if ($return->creditNote && $items !== (string) $return->creditNote->total) {
                        $failures[] = "Return {$return->id} credit note drift.";
                    }
                }
            });

        if ($failures !== []) {
            foreach ($failures as $failure) {
                $this->error($failure);
            }
            $this->error('Money reconciliation failed; no data was modified.');

            return self::FAILURE;
        }

        $this->info('Money reconciliation passed; no monetary drift detected.');

        return self::SUCCESS;
    }
}
