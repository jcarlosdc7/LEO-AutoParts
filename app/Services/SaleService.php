<?php

namespace App\Services;

use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SalePayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function create(array $cart, int $customerId, int $paymentMethodId, ?float $receivedAmount, User $user): Sale
    {
        if (! $user->hasAnyRole(['Administrador', 'Vendedor'])) {
            abort(403, 'No tiene permiso para registrar ventas.');
        }

        if (empty($cart)) {
            throw ValidationException::withMessages(['invoice' => 'Debe agregar al menos un producto.']);
        }

        return DB::transaction(function () use ($cart, $customerId, $paymentMethodId, $receivedAmount, $user) {
            $normalized = collect($cart)->map(function (array $item) {
                $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);
                if (! $quantity || $quantity < 1) {
                    throw ValidationException::withMessages(['invoice' => 'Las cantidades deben ser números enteros positivos.']);
                }

                return ['id' => (int) ($item['id'] ?? 0), 'quantity' => $quantity];
            })->groupBy('id')->map(fn ($items) => $items->sum('quantity'));

            $products = Product::query()
                ->whereIn('id', $normalized->keys())
                ->where('is_active', true)
                ->orderBy('id')
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

            $paymentMethod = PaymentMethod::query()
                ->whereKey($paymentMethodId)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (! $paymentMethod) {
                throw ValidationException::withMessages([
                    'paymentMethodId' => 'El método de pago no está disponible.',
                ]);
            }

            $session = CashSession::query()
                ->where('user_id', $user->id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->latest('opened_at')
                ->first();

            if (! $session) {
                throw ValidationException::withMessages(['cash' => 'Debe abrir una caja antes de registrar ventas.']);
            }

            if ($paymentMethod->affects_cash_drawer && ($receivedAmount === null || $receivedAmount < $total)) {
                throw ValidationException::withMessages(['amount' => 'El efectivo recibido debe cubrir el total.']);
            }

            $received = $paymentMethod->affects_cash_drawer ? round((float) $receivedAmount, 2) : $total;
            $change = $paymentMethod->affects_cash_drawer ? round($received - $total, 2) : 0.0;

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
                $lineTotal = round((float) $product->price * $quantity, 2);

                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'total' => $lineTotal,
                ]);

                $this->inventory->consume(
                    $product,
                    $quantity,
                    InventoryService::SALE,
                    $user,
                    $sale,
                    "Venta #{$sale->id}",
                );
            }

            SalePayment::create([
                'sale_id' => $sale->id,
                'payment_method_id' => $paymentMethodId,
                'cash_session_id' => $session?->id,
                'amount' => $total,
                'received_amount' => $received,
                'change_amount' => $change,
            ]);

            if ($session && $paymentMethod->affects_cash_drawer) {
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

    public function void(int|Sale $sale, string $reason, User $actor): Sale
    {
        if (! $actor->is_active || ! $actor->hasRole('Administrador')) {
            abort(403, 'Solo un administrador activo puede anular ventas.');
        }

        $reason = trim($reason);
        if (mb_strlen($reason) < 10 || mb_strlen($reason) > 1000) {
            throw ValidationException::withMessages([
                'voidReason' => 'El motivo de anulación debe contener entre 10 y 1000 caracteres.',
            ]);
        }

        $saleId = $sale instanceof Sale ? $sale->getKey() : $sale;

        return DB::transaction(function () use ($saleId, $reason, $actor) {
            $lockedSale = Sale::query()->lockForUpdate()->findOrFail($saleId);

            if ($lockedSale->status !== 'completed') {
                throw ValidationException::withMessages([
                    'voidReason' => 'La venta ya no está disponible para anulación.',
                ]);
            }

            $details = SaleDetail::query()
                ->where('sale_id', $lockedSale->id)
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get();

            $products = Product::query()
                ->whereIn('id', $details->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $details->pluck('product_id')->unique()->count()) {
                throw ValidationException::withMessages([
                    'voidReason' => 'No fue posible localizar todos los productos históricos de la venta.',
                ]);
            }

            $cashMovement = CashMovement::query()
                ->where('reference_type', Sale::class)
                ->where('reference_id', $lockedSale->id)
                ->where('type', 'sale')
                ->lockForUpdate()
                ->first();

            $cashSession = null;
            if ($cashMovement) {
                $cashSession = CashSession::query()->lockForUpdate()->findOrFail($cashMovement->cash_session_id);
                if ($cashSession->status !== 'open') {
                    throw ValidationException::withMessages([
                        'voidReason' => 'La caja original ya fue cerrada. Registre una devolución y reembolso en lugar de anular.',
                    ]);
                }
            }

            foreach ($details->groupBy('product_id') as $productId => $productDetails) {
                $this->inventory->restore(
                    $products[$productId],
                    (int) $productDetails->sum('quantity'),
                    InventoryService::SALE_VOID,
                    $actor,
                    $lockedSale,
                    $reason,
                );
            }

            if ($cashMovement && $cashSession) {
                CashMovement::create([
                    'cash_session_id' => $cashSession->id,
                    'user_id' => $actor->id,
                    'type' => 'refund',
                    'amount' => $cashMovement->amount,
                    'reason' => "Anulación de venta #{$lockedSale->id}",
                    'notes' => $reason,
                    'reference_type' => Sale::class,
                    'reference_id' => $lockedSale->id,
                ]);
            }

            $before = $lockedSale->getAttributes();
            $lockedSale->update([
                'status' => 'voided',
                'void_reason' => $reason,
                'voided_by' => $actor->id,
                'voided_at' => now(),
            ]);

            AuditService::record(
                'sale.voided',
                $lockedSale,
                $before,
                $lockedSale->getAttributes(),
                $actor->id,
            );

            return $lockedSale->load([
                'customer', 'saleDetails.product', 'salePayments', 'cashSession',
            ]);
        }, 3);
    }
}
