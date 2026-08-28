<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['category_id']);
            $table->foreign('supplier_id')->references('id')->on('suppliers')->restrictOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
            $table->index(['is_active', 'category_id', 'stock'], 'products_inventory_filter_index');
        });

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        DB::table('warehouses')->insert([
            'code' => 'MAIN',
            'name' => 'Almacén principal',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $warehouseId = DB::table('warehouses')->where('code', 'MAIN')->value('id');

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('product_id')->constrained()->restrictOnDelete();
            $table->string('operation_key', 191)->nullable()->after('warehouse_id')->unique();
            $table->timestamp('occurred_at')->nullable()->after('notes');
            $table->decimal('unit_cost', 12, 2)->nullable()->after('occurred_at');
            $table->decimal('total_cost', 12, 2)->nullable()->after('unit_cost');
        });

        DB::table('stock_movements')->orderBy('id')->each(function (object $movement) use ($warehouseId): void {
            DB::table('stock_movements')->where('id', $movement->id)->update([
                'warehouse_id' => $warehouseId,
                'operation_key' => 'legacy-movement-'.$movement->id,
                'occurred_at' => $movement->created_at,
            ]);
        });

        $negativeProduct = DB::table('products')->where('stock', '<', 0)->first();
        if ($negativeProduct) {
            throw new RuntimeException("No se puede crear el ledger: producto {$negativeProduct->id} tiene stock negativo.");
        }

        DB::table('products')->orderBy('id')->each(function (object $product) use ($warehouseId): void {
            $movementTotal = (int) DB::table('stock_movements')->where('product_id', $product->id)->sum('quantity');
            $openingBalance = (int) $product->stock - $movementTotal;
            if ($openingBalance === 0) {
                return;
            }

            $firstMovementAt = DB::table('stock_movements')->where('product_id', $product->id)->min('occurred_at');
            $occurredAt = $firstMovementAt
                ? date('Y-m-d H:i:s', strtotime((string) $firstMovementAt) - 1)
                : now();

            DB::table('stock_movements')->insert([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'operation_key' => 'initial-balance-'.$product->id,
                'user_id' => null,
                'type' => 'initial_balance',
                'quantity' => $openingBalance,
                'stock_before' => 0,
                'stock_after' => $openingBalance,
                'reference_type' => null,
                'reference_id' => null,
                'notes' => 'Saldo existente al introducir el ledger; no representa una compra histórica.',
                'occurred_at' => $occurredAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable(false)->change();
            $table->timestamp('occurred_at')->nullable(false)->change();
            $table->index(['product_id', 'warehouse_id', 'occurred_at'], 'stock_movements_kardex_index');
            $table->index(['type', 'occurred_at']);
        });

        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->uuid('operation_id')->unique();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->string('type');
            $table->integer('quantity');
            $table->text('reason');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->index(['product_id', 'occurred_at']);
        });

        DB::statement('ALTER TABLE products ADD CONSTRAINT products_stock_nonnegative CHECK (stock >= 0)');
        DB::statement('ALTER TABLE products ADD CONSTRAINT products_min_stock_nonnegative CHECK (min_stock >= 0)');
        DB::statement('ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_quantity_nonzero CHECK (quantity <> 0)');
        DB::statement('ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_balances_nonnegative CHECK (stock_before >= 0 AND stock_after >= 0)');
        DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_type_valid CHECK (type IN ('initial_balance','purchase_receipt','sale','sale_void','customer_return','supplier_return','adjustment_in','adjustment_out','damage','loss','transfer_in','transfer_out','stock_count_correction'))");
        DB::statement('ALTER TABLE inventory_adjustments ADD CONSTRAINT inventory_adjustments_quantity_nonzero CHECK (quantity <> 0)');
        DB::statement("ALTER TABLE inventory_adjustments ADD CONSTRAINT inventory_adjustments_type_valid CHECK (type IN ('adjustment_in','adjustment_out','stock_count_correction'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE inventory_adjustments DROP CHECK inventory_adjustments_type_valid');
        DB::statement('ALTER TABLE inventory_adjustments DROP CHECK inventory_adjustments_quantity_nonzero');
        DB::statement('ALTER TABLE stock_movements DROP CHECK stock_movements_type_valid');
        DB::statement('ALTER TABLE stock_movements DROP CHECK stock_movements_balances_nonnegative');
        DB::statement('ALTER TABLE stock_movements DROP CHECK stock_movements_quantity_nonzero');
        DB::statement('ALTER TABLE products DROP CHECK products_min_stock_nonnegative');
        DB::statement('ALTER TABLE products DROP CHECK products_stock_nonnegative');
        Schema::dropIfExists('inventory_adjustments');
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
        });
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['type', 'occurred_at']);
            $table->dropUnique(['operation_key']);
            $table->dropColumn(['warehouse_id', 'operation_key', 'occurred_at', 'unit_cost', 'total_cost']);
        });
        Schema::dropIfExists('warehouses');
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_inventory_filter_index');
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['category_id']);
            $table->foreign('supplier_id')->references('id')->on('suppliers')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
        });
    }
};
