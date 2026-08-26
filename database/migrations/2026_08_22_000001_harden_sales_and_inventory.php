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
            $table->decimal('cost_price', 12, 2)->default(0)->after('price');
            $table->boolean('is_active')->default(true)->after('image_path');
            $table->softDeletes();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->unique()->after('id');
            $table->string('status')->default('posted')->after('payment_method_id');
            $table->decimal('subtotal', 12, 2)->default(0)->after('total');
            $table->decimal('discount_total', 12, 2)->default(0)->after('subtotal');
            $table->decimal('tax_total', 12, 2)->default(0)->after('discount_total');
            $table->decimal('amount_received', 12, 2)->nullable()->after('change');
            $table->decimal('balance', 12, 2)->default(0)->after('amount_received');
            $table->string('customer_name_snapshot')->nullable();
            $table->string('customer_document_snapshot')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable();
            $table->softDeletes();
        });

        Schema::table('sale_details', function (Blueprint $table) {
            $table->string('product_code_snapshot')->nullable();
            $table->string('product_name_snapshot')->nullable();
            $table->decimal('unit_cost', 12, 2)->default(0);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('status')->default('posted');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('type');
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->nullableMorphs('reference');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->index(['product_id', 'occurred_at']);
        });

        DB::table('sales')->orderBy('id')->each(function (object $sale): void {
            $customer = DB::table('customers')->where('id', $sale->customer_id)->first(['name', 'dni_ruc']);
            DB::table('sales')->where('id', $sale->id)->update([
                'invoice_number' => sprintf('LEG-%06d', $sale->id),
                'status' => 'posted', 'subtotal' => $sale->total,
                'amount_received' => $sale->amount ?? $sale->total, 'balance' => 0,
                'customer_name_snapshot' => $customer?->name,
                'customer_document_snapshot' => $customer?->dni_ruc,
            ]);
        });

        DB::table('sale_details')->orderBy('id')->each(function (object $detail): void {
            $product = DB::table('products')->where('id', $detail->product_id)->first(['code', 'name', 'cost_price']);
            DB::table('sale_details')->where('id', $detail->id)->update([
                'product_code_snapshot' => $product?->code,
                'product_name_snapshot' => $product?->name,
                'unit_cost' => $product?->cost_price ?? 0,
            ]);
        });

        DB::table('products')->orderBy('id')->each(function (object $product): void {
            DB::table('inventory_movements')->insert([
                'product_id' => $product->id, 'type' => 'opening_balance',
                'quantity' => $product->stock, 'stock_before' => 0, 'stock_after' => $product->stock,
                'unit_cost' => $product->cost_price,
                'note' => 'Saldo inicial registrado al fortalecer el control de inventario.',
                'occurred_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('received_by');
            $table->dropColumn(['status', 'reference', 'notes', 'voided_at']);
        });
        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropColumn(['product_code_snapshot', 'product_name_snapshot', 'unit_cost']);
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropUnique(['invoice_number']); $table->dropSoftDeletes();
            $table->dropColumn(['invoice_number', 'status', 'subtotal', 'discount_total', 'tax_total', 'amount_received', 'balance', 'customer_name_snapshot', 'customer_document_snapshot', 'cancelled_at', 'cancellation_reason']);
        });
        foreach (['customers', 'suppliers'] as $name) {
            Schema::table($name, function (Blueprint $table) { $table->dropSoftDeletes(); $table->dropColumn('is_active'); });
        }
        Schema::table('products', function (Blueprint $table) {
            $table->dropSoftDeletes(); $table->dropColumn(['cost_price', 'is_active']);
        });
    }
};
