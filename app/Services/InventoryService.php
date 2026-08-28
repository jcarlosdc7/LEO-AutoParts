<?php

namespace App\Services;

use App\Models\InventoryAdjustment;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public const INITIAL_BALANCE = 'initial_balance';

    public const SALE = 'sale';

    public const SALE_VOID = 'sale_void';

    public const CUSTOMER_RETURN = 'customer_return';

    public const ADJUSTMENT_IN = 'adjustment_in';

    public const ADJUSTMENT_OUT = 'adjustment_out';

    public const STOCK_COUNT_CORRECTION = 'stock_count_correction';

    public function consume(int|Product $product, int $quantity, string $type, User $actor, Model $reference, ?string $reason = null): StockMovement
    {
        if (! in_array($type, [self::SALE, self::ADJUSTMENT_OUT], true)) {
            throw new \InvalidArgumentException('Tipo de salida de inventario inválido.');
        }

        return $this->move($product, -$this->positiveQuantity($quantity), $type, $actor, $reference, $reason);
    }

    public function restore(int|Product $product, int $quantity, string $type, User $actor, Model $reference, ?string $reason = null): StockMovement
    {
        if (! in_array($type, [self::SALE_VOID, self::CUSTOMER_RETURN, self::ADJUSTMENT_IN], true)) {
            throw new \InvalidArgumentException('Tipo de entrada de inventario inválido.');
        }

        return $this->move($product, $this->positiveQuantity($quantity), $type, $actor, $reference, $reason);
    }

    public function adjust(int $productId, string $mode, mixed $value, string $reason, User $actor, string $operationId): InventoryAdjustment
    {
        $this->authorizeAdjustment($actor);
        $reason = $this->validReason($reason);
        if (! Str::isUuid($operationId)) {
            throw ValidationException::withMessages(['adjustmentOperationId' => 'La clave de idempotencia no es válida.']);
        }
        if (! in_array($mode, ['increase', 'decrease', 'count'], true)) {
            throw ValidationException::withMessages(['adjustmentMode' => 'El tipo de ajuste no es válido.']);
        }
        $value = filter_var($value, FILTER_VALIDATE_INT);
        if ($value === false || $value < 0 || $value > 1000000 || ($mode !== 'count' && $value === 0)) {
            throw ValidationException::withMessages(['adjustmentValue' => 'La cantidad debe ser un entero válido entre 1 y 1,000,000.']);
        }

        return DB::transaction(function () use ($productId, $mode, $value, $reason, $actor, $operationId) {
            $existing = InventoryAdjustment::query()->where('operation_id', $operationId)->first();
            if ($existing) {
                abort_unless($existing->product_id === $productId, 409, 'La clave de idempotencia pertenece a otro producto.');
                $expectedType = match ($mode) {
                    'increase' => self::ADJUSTMENT_IN,
                    'decrease' => self::ADJUSTMENT_OUT,
                    'count' => self::STOCK_COUNT_CORRECTION,
                    default => null,
                };
                $sameQuantity = $mode === 'count'
                    || $existing->quantity === ($mode === 'increase' ? $value : -$value);
                abort_unless($existing->type === $expectedType && $sameQuantity && $existing->reason === $reason, 409, 'El reintento no coincide con el ajuste original.');

                return $existing->load('movement');
            }

            $product = Product::query()->lockForUpdate()->findOrFail($productId);
            if (! $product->is_active) {
                throw ValidationException::withMessages(['adjustmentProductId' => 'No se puede ajustar un producto inactivo.']);
            }
            $warehouse = $this->mainWarehouse();
            $delta = match ($mode) {
                'increase' => $value,
                'decrease' => -$value,
                'count' => $value - $product->stock,
            };
            if ($delta === 0) {
                throw ValidationException::withMessages(['adjustmentValue' => 'El ajuste no produce ningún cambio de stock.']);
            }
            $type = match (true) {
                $mode === 'count' => self::STOCK_COUNT_CORRECTION,
                $delta > 0 => self::ADJUSTMENT_IN,
                default => self::ADJUSTMENT_OUT,
            };

            $adjustment = InventoryAdjustment::create([
                'operation_id' => $operationId,
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'type' => $type,
                'quantity' => $delta,
                'reason' => $reason,
                'created_by' => $actor->id,
                'occurred_at' => now(),
            ]);
            $movement = $this->applyLocked(
                $product,
                $warehouse,
                $delta,
                $type,
                $actor,
                $adjustment,
                $reason,
                'adjustment:'.$adjustment->id,
            );

            AuditService::record(
                $mode === 'count' ? 'inventory.count_corrected' : 'inventory.adjusted',
                $adjustment,
                ['stock' => $movement->stock_before],
                ['stock' => $movement->stock_after, 'quantity' => $delta, 'reason' => $reason],
                $actor->id,
            );

            return $adjustment->load('movement');
        }, 3);
    }

    public function initialize(Product $product, int $quantity, ?User $actor = null): ?StockMovement
    {
        if ($quantity < 0 || $quantity > 1000000) {
            throw ValidationException::withMessages(['stock' => 'El saldo inicial no es válido.']);
        }
        if ($actor === null && ! app()->runningInConsole()) {
            abort(403, 'El saldo inicial requiere autorización.');
        }
        if ($quantity === 0) {
            return null;
        }

        return DB::transaction(function () use ($product, $quantity, $actor) {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);
            if ($locked->stock !== 0 || $locked->stockMovements()->exists()) {
                throw ValidationException::withMessages(['stock' => 'El producto ya posee historia de inventario.']);
            }
            $warehouse = $this->mainWarehouse();

            return $this->applyLocked(
                $locked,
                $warehouse,
                $quantity,
                self::INITIAL_BALANCE,
                $actor,
                null,
                'Saldo inicial explícito del producto.',
                'initial-balance:'.$locked->id,
            );
        }, 3);
    }

    private function move(int|Product $product, int $quantity, string $type, User $actor, Model $reference, ?string $reason): StockMovement
    {
        $productId = $product instanceof Product ? $product->id : $product;

        return DB::transaction(function () use ($productId, $quantity, $type, $actor, $reference, $reason) {
            $locked = Product::query()->lockForUpdate()->findOrFail($productId);
            if ($type === self::SALE && ! $locked->is_active) {
                throw ValidationException::withMessages(['invoice' => 'El producto ya no está disponible.']);
            }
            $warehouse = $this->mainWarehouse();
            $operationKey = sprintf('%s:%d:product:%d', Str::snake(class_basename($reference)), $reference->getKey(), $locked->id).':'.$type;
            $existing = StockMovement::query()->where('operation_key', $operationKey)->first();
            if ($existing) {
                abort_unless(
                    $existing->quantity === $quantity
                    && $existing->type === $type
                    && $existing->reference_type === $reference->getMorphClass()
                    && $existing->reference_id === $reference->getKey(),
                    409,
                    'El movimiento reintentado no coincide con la operación original.',
                );

                return $existing;
            }

            return $this->applyLocked($locked, $warehouse, $quantity, $type, $actor, $reference, $reason, $operationKey);
        }, 3);
    }

    private function applyLocked(Product $product, Warehouse $warehouse, int $quantity, string $type, ?User $actor, ?Model $reference, ?string $reason, string $operationKey): StockMovement
    {
        $before = (int) $product->stock;
        $after = $before + $quantity;
        if ($after < 0) {
            throw ValidationException::withMessages(['inventory' => "Stock insuficiente para {$product->name}."]);
        }

        Product::withoutEvents(fn () => $product->forceFill(['stock' => $after])->save());

        return StockMovement::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'operation_key' => $operationKey,
            'user_id' => $actor?->id,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $before,
            'stock_after' => $after,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'notes' => $reason,
            'occurred_at' => now(),
        ]);
    }

    private function mainWarehouse(): Warehouse
    {
        $warehouse = Warehouse::query()->where('code', 'MAIN')->where('is_active', true)->first();
        if (! $warehouse) {
            throw ValidationException::withMessages(['inventory' => 'El almacén principal no está disponible.']);
        }

        return $warehouse;
    }

    private function positiveQuantity(int $quantity): int
    {
        if ($quantity < 1 || $quantity > 1000000) {
            throw ValidationException::withMessages(['inventory' => 'La cantidad debe ser un entero entre 1 y 1,000,000.']);
        }

        return $quantity;
    }

    private function authorizeAdjustment(User $actor): void
    {
        abort_unless($actor->is_active && $actor->hasRole('Administrador'), 403, 'Solo un administrador activo puede ajustar inventario.');
    }

    private function validReason(string $reason): string
    {
        $reason = trim($reason);
        if (mb_strlen($reason) < 10 || mb_strlen($reason) > 1000) {
            throw ValidationException::withMessages(['adjustmentReason' => 'El motivo debe contener entre 10 y 1000 caracteres.']);
        }

        return $reason;
    }
}
