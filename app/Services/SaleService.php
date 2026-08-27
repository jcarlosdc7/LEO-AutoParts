<?php

namespace App\Services;

use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SalePayment;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function create(array $cart, int $customerId, int $paymentMethodId, ?float $receivedAmount, User $user): Sale
    {
        if (!$user->hasAnyRole(['Administrador', 'Vendedor'])) {
            abort(403, 'No tiene permiso para registrar ventas.');
        }

        if (empty($cart)) {
            throw ValidationException::withMessages(['invoice' => 'Debe agregar al menos un producto.']);
        }

        return DB::transaction(function () use ($cart, $customerId, $paymentMethodId, $receivedAmount, $user) {
            $session = CashSession::query()
                ->where('user_id', $user->id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->latest('opened_at')
                ->first();

            if (!$session) {
                throw ValidationException::withMessages(['cash' => 'Debe abrir una caja antes de registrar ventas.']);
            }

            $normalized = collect($cart)->map(function (array $item) {
                $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);
                if (!$quantity || $quantity < 1) {
                    throw ValidationException::withMessages(['invoice' => 'Las cantidades deben ser números enteros positivos.']);
                }
                return ['id' => (int) ($item['id'] ?? 0), 'quantity' => $quantity];
            })->groupBy('id')->map(fn ($items) => $items->sum('quantity'));

            $products = Product::query()
                ->whereIn('id', $normalized->keys())
                ->where('is_active', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $normalized->count()) {
                throw ValidationException::withMessages(['invoice' => 'Uno o más productos ya no están disponibles.']);
            }

            $total = 0.0;
            foreach ($normalized as $productId => $quantity) {
                $product = $products[$productId];
                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages(['invoice' => "Stock insuficiente para {$product->name}."]);
                }
                $total += round((float) $product->price * $quantity, 2);
            }
            $total = round($total, 2);

            if ($paymentMethodId === 1 && ($receivedAmount === null || $receivedAmount < $total)) {
                throw ValidationException::withMessages(['amount' => 'El efectivo recibido debe cubrir el total.']);
            }

            $received = $paymentMethodId === 1 ? round((float) $receivedAmount, 2) : $total;
            $change = $paymentMethodId === 1 ? round($received - $total, 2) : 0.0;

            $sale = Sale::create([
                'customer_id' => $customerId,
                'user_id' => $user->id,
                'cash_session_id' => $session?->id,
                'total' => $total,
                'amount' => $received,
                'change' => $change,
                'sale_date' => now(),
                'payment_method_id' => $paymentMethodId,
                'status' => 'completed',
            ]);

            foreach ($normalized as $productId => $quantity) {
                $product = $products[$productId];
                $before = $product->stock;
                $lineTotal = round((float) $product->price * $quantity, 2);

                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'total' => $lineTotal,
                ]);

                $product->decrement('stock', $quantity);
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'type' => 'sale',
                    'quantity' => -$quantity,
                    'stock_before' => $before,
                    'stock_after' => $before - $quantity,
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                ]);
            }

            SalePayment::create([
                'sale_id' => $sale->id,
                'payment_method_id' => $paymentMethodId,
                'cash_session_id' => $session?->id,
                'amount' => $total,
                'received_amount' => $received,
                'change_amount' => $change,
            ]);

            if ($session && $paymentMethodId === 1) {
                CashMovement::create([
                    'cash_session_id' => $session->id,
                    'user_id' => $user->id,
                    'type' => 'sale',
                    'amount' => $total,
                    'reason' => "Venta #{$sale->id}",
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                ]);
            }

            return $sale->load(['customer', 'saleDetails.product', 'salePayments']);
        }, 3);
    }
}
