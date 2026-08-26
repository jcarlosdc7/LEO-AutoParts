<?php

namespace App\Services\Purchases;

use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function create(User $user, int $supplierId, array $items, mixed $amountPaid = 0, ?string $dueDate = null, ?string $notes = null): Purchase
    {
        if ($items === []) {
            throw ValidationException::withMessages(['items' => 'Agregue al menos un producto.']);
        }

        return DB::transaction(function () use ($user, $supplierId, $items, $amountPaid, $dueDate, $notes): Purchase {
            $supplier = Supplier::lockForUpdate()->find($supplierId);
            if (! $supplier) {
                throw ValidationException::withMessages(['supplierId' => 'Seleccione un proveedor válido.']);
            }

            $normalized = [];
            foreach ($items as $item) {
                $id = filter_var($item['product_id'] ?? null, FILTER_VALIDATE_INT);
                $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);
                $cost = round((float) ($item['unit_cost'] ?? 0), 2);
                if (! $id || ! $quantity || $quantity < 1 || $cost <= 0) {
                    throw ValidationException::withMessages(['items' => 'Revise cantidades y costos de la compra.']);
                }
                $normalized[] = compact('id', 'quantity', 'cost');
            }

            $products = Product::whereIn('id', collect($normalized)->pluck('id'))->lockForUpdate()->get()->keyBy('id');
            if ($products->count() !== collect($normalized)->pluck('id')->unique()->count()) {
                throw ValidationException::withMessages(['items' => 'Uno de los productos ya no existe.']);
            }

            $total = round(collect($normalized)->sum(fn ($line) => $line['quantity'] * $line['cost']), 2);
            $paid = round((float) $amountPaid, 2);
            if ($paid < 0 || $paid > $total) {
                throw ValidationException::withMessages(['amountPaid' => 'El pago debe estar entre cero y el total.']);
            }

            $purchase = Purchase::create([
                'purchase_number' => 'TEMP-'.uniqid(), 'supplier_id' => $supplier->id, 'user_id' => $user->id,
                'purchase_date' => now(), 'status' => $paid >= $total ? 'paid' : 'pending',
                'subtotal' => $total, 'discount_total' => 0, 'tax_total' => 0, 'total' => $total,
                'amount_paid' => $paid, 'balance' => $total - $paid, 'due_date' => $dueDate ?: null, 'notes' => $notes,
            ]);
            $purchase->update(['purchase_number' => 'COM-'.now()->format('Y').'-'.str_pad((string) $purchase->id, 6, '0', STR_PAD_LEFT)]);

            foreach ($normalized as $line) {
                $product = $products[$line['id']];
                $before = $product->stock;
                $detail = PurchaseDetail::create([
                    'purchase_id' => $purchase->id, 'product_id' => $product->id,
                    'quantity' => $line['quantity'], 'unit_cost' => $line['cost'],
                    'total' => $line['quantity'] * $line['cost'],
                    'product_code_snapshot' => $product->code, 'product_name_snapshot' => $product->name,
                ]);
                $product->update(['stock' => $before + $line['quantity'], 'cost_price' => $line['cost']]);
                InventoryMovement::create([
                    'product_id' => $product->id, 'type' => 'purchase', 'quantity' => $line['quantity'],
                    'stock_before' => $before, 'stock_after' => $before + $line['quantity'], 'unit_cost' => $line['cost'],
                    'reference_type' => PurchaseDetail::class, 'reference_id' => $detail->id,
                    'user_id' => $user->id, 'note' => "Entrada por {$purchase->purchase_number}", 'occurred_at' => now(),
                ]);
            }
            if ($paid > 0) {
                $cash = CashSession::where('status', 'open')->lockForUpdate()->first();
                if ($cash) {
                    CashMovement::create([
                        'cash_session_id' => $cash->id, 'type' => 'expense', 'amount' => $paid,
                        'description' => "Pago de compra {$purchase->purchase_number}", 'reference_type' => Purchase::class,
                        'reference_id' => $purchase->id, 'user_id' => $user->id, 'occurred_at' => now(),
                    ]);
                }
            }

            return $purchase->fresh(['supplier', 'user', 'details.product']);
        }, 3);
    }
}
