<?php

namespace App\Services;

use App\Models\CashMovement;
use App\Models\CreditNote;
use App\Models\CreditNoteItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\User;
use App\Support\Decimal;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReturnService
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly CashService $cash,
    ) {}

    public function process(int|Sale $sale, array $items, int $refundMethodId, string $reason, User $actor, string $operationId, ?string $reference = null): SaleReturn
    {
        $this->authorize($actor);
        $reason = trim($reason);
        if (mb_strlen($reason) < 10 || mb_strlen($reason) > 1000) {
            throw ValidationException::withMessages(['returnReason' => 'El motivo debe contener entre 10 y 1000 caracteres.']);
        }
        if (! Str::isUuid($operationId)) {
            throw ValidationException::withMessages(['operationId' => 'La clave de idempotencia no es válida.']);
        }
        if ($items === []) {
            throw ValidationException::withMessages(['returnItems' => 'Seleccione al menos una línea para devolver.']);
        }
        $reference = $reference === null ? null : trim($reference);
        if ($reference !== null && mb_strlen($reference) > 255) {
            throw ValidationException::withMessages(['refundReference' => 'La referencia no puede exceder 255 caracteres.']);
        }

        $saleId = $sale instanceof Sale ? $sale->getKey() : $sale;

        return DB::transaction(function () use ($saleId, $items, $refundMethodId, $reason, $actor, $operationId, $reference) {
            $lockedSale = Sale::query()->lockForUpdate()->findOrFail($saleId);
            if ($lockedSale->status === 'voided') {
                throw ValidationException::withMessages(['returnReason' => 'No se admiten devoluciones sobre una venta anulada.']);
            }

            $existing = SaleReturn::query()->where('operation_id', $operationId)->first();
            if ($existing) {
                abort_unless($existing->sale_id === $lockedSale->id, 409, 'La clave de idempotencia pertenece a otra venta.');

                return $existing->load(['items', 'refund.paymentMethod', 'creditNote.items']);
            }

            $normalized = collect($items)->map(function (array $item): array {
                $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);
                if (! $quantity || $quantity < 1) {
                    throw ValidationException::withMessages(['returnItems' => 'Las cantidades deben ser enteros positivos.']);
                }
                $condition = (string) ($item['condition'] ?? 'sellable');
                if (! in_array($condition, ['sellable', 'damaged', 'defective', 'quarantine'], true)) {
                    throw ValidationException::withMessages(['returnItems' => 'La condición del producto no es válida.']);
                }

                return [
                    'sale_detail_id' => (int) ($item['sale_detail_id'] ?? 0),
                    'quantity' => $quantity,
                    'restock' => filter_var($item['restock'] ?? false, FILTER_VALIDATE_BOOL),
                    'condition' => $condition,
                ];
            });
            if ($normalized->pluck('sale_detail_id')->duplicates()->isNotEmpty()) {
                throw ValidationException::withMessages(['returnItems' => 'No repita líneas de venta en la misma devolución.']);
            }

            $details = SaleDetail::query()
                ->where('sale_id', $lockedSale->id)
                ->whereIn('id', $normalized->pluck('sale_detail_id'))
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get()->keyBy('id');
            if ($details->count() !== $normalized->count()) {
                throw ValidationException::withMessages(['returnItems' => 'Una o más líneas no pertenecen a la venta.']);
            }

            $alreadyReturned = SaleReturnItem::query()
                ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
                ->where('sale_returns.sale_id', $lockedSale->id)
                ->where('sale_returns.status', 'completed')
                ->whereIn('sale_return_items.sale_detail_id', $details->keys())
                ->groupBy('sale_return_items.sale_detail_id')
                ->selectRaw('sale_return_items.sale_detail_id, SUM(sale_return_items.quantity) as quantity')
                ->pluck('quantity', 'sale_detail_id');

            $total = Money::zero();
            foreach ($normalized as $item) {
                $detail = $details[$item['sale_detail_id']];
                $available = $detail->quantity - (int) ($alreadyReturned[$detail->id] ?? 0);
                if ($item['quantity'] > $available) {
                    throw ValidationException::withMessages(['returnItems' => "La cantidad excede lo disponible para {$detail->product_id}."]);
                }
                if ($item['restock'] && $item['condition'] !== 'sellable') {
                    throw ValidationException::withMessages(['returnItems' => 'Solo productos vendibles pueden regresar al stock disponible.']);
                }
                $total = $total->add(Money::fromUnitPrice((string) $detail->price, (int) $item['quantity']));
            }

            $products = Product::query()->whereIn('id', $details->pluck('product_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $paymentMethod = PaymentMethod::query()->whereKey($refundMethodId)->where('is_active', true)->lockForUpdate()->first();
            if (! $paymentMethod) {
                throw ValidationException::withMessages(['refundMethod' => 'El método de reembolso no está disponible.']);
            }
            if ($paymentMethod->requires_reference && blank($reference)) {
                throw ValidationException::withMessages(['refundReference' => 'Este método exige una referencia.']);
            }

            $cashSession = null;
            if ($paymentMethod->affects_cash_drawer) {
                $cashSession = $this->cash->activeSessionFor($actor, lock: true);
                if (! $cashSession) {
                    throw ValidationException::withMessages(['refundMethod' => 'Se requiere una caja activa para reembolsar efectivo.']);
                }
                if (Decimal::compare($this->cash->expectedCash($cashSession), $total->amount(), Decimal::STORAGE_SCALE) < 0) {
                    throw ValidationException::withMessages(['refundMethod' => 'La caja no dispone de efectivo suficiente para el reembolso.']);
                }
            }

            $saleReturn = SaleReturn::create([
                'operation_id' => $operationId,
                'sale_id' => $lockedSale->id,
                'return_number' => $this->nextNumber('return_sequences', 'DEV'),
                'status' => 'completed',
                'reason' => $reason,
                'requested_by' => $actor->id,
                'authorized_by' => $actor->id,
                'completed_at' => now(),
            ]);

            foreach ($normalized as $item) {
                $detail = $details[$item['sale_detail_id']];
                $lineTotal = Money::fromUnitPrice((string) $detail->price, (int) $item['quantity'])->amount();
                SaleReturnItem::create([
                    'sale_return_id' => $saleReturn->id, 'sale_detail_id' => $detail->id, 'product_id' => $detail->product_id,
                    'quantity' => $item['quantity'], 'unit_price' => $detail->price, 'refund_amount' => $lineTotal,
                    'condition' => $item['condition'], 'restock' => $item['restock'], 'reason' => $reason,
                ]);
            }

            $restocked = $saleReturn->items->where('restock', true)->groupBy('product_id');
            foreach ($restocked as $productId => $returnItems) {
                $this->inventory->restore(
                    $products[$productId],
                    (int) $returnItems->sum('quantity'),
                    InventoryService::CUSTOMER_RETURN,
                    $actor,
                    $saleReturn,
                    $reason,
                );
            }

            $originalPayment = $lockedSale->salePayments()->where('payment_method_id', $paymentMethod->id)->first()
                ?? $lockedSale->salePayments()->first();
            $refund = Refund::create([
                'sale_return_id' => $saleReturn->id, 'sale_id' => $lockedSale->id, 'payment_method_id' => $paymentMethod->id,
                'sale_payment_id' => $originalPayment?->id, 'cash_session_id' => $cashSession?->id, 'amount' => $total->amount(),
                'reference' => $reference, 'status' => 'completed', 'processed_by' => $actor->id, 'processed_at' => now(),
            ]);
            if ($cashSession) {
                CashMovement::forceCreate([
                    'cash_session_id' => $cashSession->id, 'user_id' => $actor->id, 'type' => 'refund', 'amount' => $total->amount(),
                    'reason' => "Devolución {$saleReturn->return_number}", 'notes' => $reason,
                    'reference_type' => Refund::class, 'reference_id' => $refund->id,
                ]);
            }

            $creditNote = CreditNote::create([
                'number' => $this->nextNumber('credit_note_sequences', 'NC'), 'sale_id' => $lockedSale->id,
                'sale_return_id' => $saleReturn->id, 'issued_at' => now(), 'currency' => 'NIO',
                'subtotal' => $total->amount(), 'tax' => '0.00', 'total' => $total->amount(), 'reason' => $reason,
                'status' => 'issued', 'created_by' => $actor->id,
            ]);
            foreach ($saleReturn->items as $returnItem) {
                $detail = $details[$returnItem->sale_detail_id];
                CreditNoteItem::create([
                    'credit_note_id' => $creditNote->id, 'sale_detail_id' => $detail->id, 'description' => $detail->product->name,
                    'quantity' => $returnItem->quantity, 'unit_price' => $returnItem->unit_price,
                    'subtotal' => $returnItem->refund_amount, 'tax' => '0.00', 'total' => $returnItem->refund_amount,
                ]);
            }

            $auditData = [
                'sale_id' => $lockedSale->id,
                'return_id' => $saleReturn->id,
                'return_number' => $saleReturn->return_number,
                'items' => $saleReturn->items->map(fn (SaleReturnItem $item) => [
                    'product_id' => $item->product_id,
                    'sale_detail_id' => $item->sale_detail_id,
                    'quantity' => $item->quantity,
                    'condition' => $item->condition,
                    'restock' => $item->restock,
                ])->all(),
                'amount' => $total->amount(),
                'refund_id' => $refund->id,
                'payment_method_id' => $paymentMethod->id,
                'payment_method_code' => $paymentMethod->code,
                'cash_session_id' => $cashSession?->id,
                'credit_note_id' => $creditNote->id,
                'credit_note_number' => $creditNote->number,
                'reason' => $reason,
            ];
            AuditService::record('return.completed', $saleReturn, [], $auditData, $actor->id);
            AuditService::record('refund.created', $refund, [], $auditData, $actor->id);
            AuditService::record('credit_note.issued', $creditNote, [], $auditData, $actor->id);

            return $saleReturn->load(['items.product', 'refund.paymentMethod', 'creditNote.items']);
        }, 3);
    }

    private function authorize(User $actor): void
    {
        abort_unless($actor->is_active && $actor->hasRole('Administrador'), 403, 'Solo un administrador activo puede procesar devoluciones.');
    }

    private function nextNumber(string $table, string $prefix): string
    {
        $year = (int) now()->format('Y');
        DB::table($table)->insertOrIgnore(['year' => $year, 'last_number' => 0, 'created_at' => now(), 'updated_at' => now()]);
        $sequence = DB::table($table)->where('year', $year)->lockForUpdate()->first();
        $next = $sequence->last_number + 1;
        DB::table($table)->where('year', $year)->update(['last_number' => $next, 'updated_at' => now()]);

        return sprintf('%s-%d-%06d', $prefix, $year, $next);
    }
}
