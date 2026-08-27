<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', fn (Blueprint $table) => $table->boolean('is_active')->default(true)->after('customer_type_id'));
        Schema::table('suppliers', fn (Blueprint $table) => $table->boolean('is_active')->default(true));

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['payment_method_id']);
            $table->foreign('customer_id')->references('id')->on('customers')->restrictOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->restrictOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['category_id']);
            $table->foreign('supplier_id')->references('id')->on('suppliers')->restrictOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
        });

        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['category_id']);
            $table->foreign('supplier_id')->references('id')->on('suppliers')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['payment_method_id']);
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->cascadeOnDelete();
        });
        Schema::table('customers', fn (Blueprint $table) => $table->dropColumn('is_active'));
        Schema::table('suppliers', fn (Blueprint $table) => $table->dropColumn('is_active'));
    }
};
