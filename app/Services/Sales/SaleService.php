<?php

namespace App\Services\Sales;

use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function create(User $user, int $customerId, int $paymentMethodId, array $items, mixed $amountReceived = null): Sale
    {
        if ($items === []) {
            throw ValidationException::withMessages(['invoiceTable' => 'Agregue al menos un producto a la venta.']);
        }
        $quantities = [];
        foreach ($items as $item) {
            $id = filter_var($item['id'] ?? null, FILTER_VALIDATE_INT);
            $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);
            if (! $id || ! $quantity || $quantity < 1) {
                throw ValidationException::withMessages(['invoiceTable' => 'La venta contiene una cantidad no válida.']);
            }
            $quantities[$id] = ($quantities[$id] ?? 0) + $quantity;
        }

        return DB::transaction(function () use ($user, $customerId, $paymentMethodId, $quantities, $amountReceived): Sale {
            $customer = Customer::where('is_active', true)->lockForUpdate()->find($customerId);
            $method = PaymentMethod::lockForUpdate()->find($paymentMethodId);
            if (! $customer || ! $method) {
                throw ValidationException::withMessages(['sale' => 'Cliente o método de pago no válido.']);
            }
            $products = Product::whereIn('id', array_keys($quantities))->where('is_active', true)->lockForUpdate()->get()->keyBy('id');
            if ($products->count() !== count($quantities)) {
                throw ValidationException::withMessages(['invoiceTable' => 'Uno o más productos ya no están disponibles.']);
            }
            $totalCents = 0;
            foreach ($quantities as $id => $quantity) {
                $product = $products[$id];
                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages(['invoiceTable' => "Stock insuficiente para {$product->name}."]);
                }
                $totalCents += $this->cents($product->price) * $quantity;
            }
            $total = $totalCents / 100;
            $received = $amountReceived === null || $amountReceived === '' ? $total : $this->money($amountReceived);
            if ($received < $total) {
                throw ValidationException::withMessages(['amount' => 'El importe recibido no puede ser menor que el total.']);
            }
            if (mb_strtolower($method->name) !== 'efectivo') {
                $received = $total;
            }
            $sale = Sale::create([
                'customer_id' => $customer->id, 'user_id' => $user->id, 'payment_method_id' => $method->id,
                'sale_date' => now(), 'status' => 'posted', 'subtotal' => $total, 'discount_total' => 0,
                'tax_total' => 0, 'total' => $total, 'amount' => $received, 'amount_received' => $received,
                'change' => $received - $total, 'balance' => 0, 'customer_name_snapshot' => $customer->name,
                'customer_document_snapshot' => $customer->dni_ruc,
            ]);
            $sale->update(['invoice_number' => 'FAC-'.now()->format('Y').'-'.str_pad((string) $sale->id, 6, '0', STR_PAD_LEFT)]);
            foreach ($quantities as $id => $quantity) {
                $product = $products[$id];
                $before = $product->stock;
                $detail = SaleDetail::create([
                    'sale_id' => $sale->id, 'product_id' => $product->id, 'quantity' => $quantity,
                    'price' => $product->price, 'total' => ($this->cents($product->price) * $quantity) / 100,
                    'unit_cost' => $product->cost_price, 'product_code_snapshot' => $product->code,
                    'product_name_snapshot' => $product->name,
                ]);
                $product->update(['stock' => $before - $quantity]);
                InventoryMovement::create([
                    'product_id' => $product->id, 'type' => 'sale', 'quantity' => -$quantity,
                    'stock_before' => $before, 'stock_after' => $before - $quantity, 'unit_cost' => $product->cost_price,
                    'reference_type' => SaleDetail::class, 'reference_id' => $detail->id, 'user_id' => $user->id,
                    'note' => "Salida por {$sale->invoice_number}", 'occurred_at' => now(),
                ]);
            }
            Payment::create([
                'sale_id' => $sale->id, 'amount' => $total, 'payment_date' => now(), 'payment_method_id' => $method->id,
                'received_by' => $user->id, 'status' => 'posted', 'reference' => $sale->invoice_number,
            ]);
            if (mb_strtolower($method->name) === 'efectivo') {
                $cash = CashSession::where('status', 'open')->lockForUpdate()->first();
                if ($cash) {
                    CashMovement::create([
                        'cash_session_id' => $cash->id, 'type' => 'income', 'amount' => $total,
                        'description' => "Venta {$sale->invoice_number}", 'reference_type' => Sale::class,
                        'reference_id' => $sale->id, 'user_id' => $user->id, 'occurred_at' => now(),
                    ]);
                }
            }

            return $sale->fresh(['customer', 'user', 'paymentMethod', 'saleDetails.product']);
        }, 3);
    }

    public function void(Sale $sale, User $user, string $reason): Sale
    {
        if ($sale->status !== 'posted' || trim($reason) === '') {
            throw ValidationException::withMessages(['sale' => 'Solo se anulan ventas contabilizadas y con motivo.']);
        }

        return DB::transaction(function () use ($sale, $user, $reason): Sale {
            $sale = Sale::lockForUpdate()->findOrFail($sale->id);
            foreach ($sale->saleDetails()->lockForUpdate()->get() as $detail) {
                $product = Product::withTrashed()->lockForUpdate()->findOrFail($detail->product_id);
                $before = $product->stock;
                $product->update(['stock' => $before + $detail->quantity]);
                InventoryMovement::create([
                    'product_id' => $product->id, 'type' => 'void_sale', 'quantity' => $detail->quantity,
                    'stock_before' => $before, 'stock_after' => $before + $detail->quantity, 'unit_cost' => $detail->unit_cost,
                    'reference_type' => Sale::class, 'reference_id' => $sale->id, 'user_id' => $user->id,
                    'note' => "Reversión de {$sale->invoice_number}: {$reason}", 'occurred_at' => now(),
                ]);
            }
            $sale->update(['status' => 'cancelled', 'cancelled_at' => now(), 'cancelled_by' => $user->id, 'cancellation_reason' => trim($reason)]);
            $sale->payments()->where('status', 'posted')->update(['status' => 'voided', 'voided_at' => now()]);

            return $sale->fresh();
        }, 3);
    }

    private function cents(mixed $value): int
    {
        return (int) round(((float) $value) * 100);
    }

    private function money(mixed $value): float
    {
        return $this->cents($value) / 100;
    }
}
