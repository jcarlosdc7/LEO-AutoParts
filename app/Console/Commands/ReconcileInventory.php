<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class ReconcileInventory extends Command
{
    protected $signature = 'inventory:reconcile {--product= : Reconcile only one product ID or code}';

    protected $description = 'Compare materialized product stock against the immutable inventory ledger';

    public function handle(): int
    {
        $query = Product::query()->select(['id', 'code', 'name', 'stock'])->orderBy('id');
        if ($filter = $this->option('product')) {
            $query->where(fn ($query) => $query->where('id', $filter)->orWhere('code', $filter));
        }

        $checked = 0;
        $discrepancies = 0;
        $query->chunkById(500, function ($products) use (&$checked, &$discrepancies): void {
            $ledgerByProduct = Product::query()
                ->whereIn('id', $products->pluck('id'))
                ->withSum('stockMovements as ledger_stock', 'quantity')
                ->get()
                ->keyBy('id');

            foreach ($products as $product) {
                $checked++;
                $ledger = (int) ($ledgerByProduct[$product->id]->ledger_stock ?? 0);
                $stored = (int) $product->stock;
                if ($stored !== $ledger) {
                    $discrepancies++;
                    $this->error(sprintf(
                        'Product %s (#%d) stored: %d ledger: %d difference: %+d',
                        $product->code,
                        $product->id,
                        $stored,
                        $ledger,
                        $stored - $ledger,
                    ));
                }
            }
        });

        if ($discrepancies > 0) {
            $this->error("FAIL: {$checked} products reconciled; {$discrepancies} discrepancies.");

            return self::FAILURE;
        }

        $this->info("PASS: {$checked} products reconciled; 0 discrepancies.");

        return self::SUCCESS;
    }
}
