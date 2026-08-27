<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_sequences', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->primary();
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();
        });

        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->uuid('operation_id')->unique();
            $table->foreignId('sale_id')->constrained()->restrictOnDelete();
            $table->string('return_number')->unique();
            $table->string('status')->default('completed');
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('authorized_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('completed_at');
            $table->timestamps();
            $table->index(['sale_id', 'status']);
            $table->index(['created_at', 'status']);
        });

        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_return_id')->constrained()->restrictOnDelete();
            $table->foreignId('sale_detail_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('refund_amount', 12, 2);
            $table->string('condition');
            $table->boolean('restock');
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->unique(['sale_return_id', 'sale_detail_id']);
            $table->index(['sale_detail_id', 'created_at']);
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_return_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('sale_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_method_id')->constrained()->restrictOnDelete();
            $table->foreignId('sale_payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cash_session_id')->nullable()->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('reference')->nullable();
            $table->string('status')->default('completed');
            $table->foreignId('processed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('processed_at');
            $table->timestamps();
            $table->index(['sale_id', 'status']);
        });

        Schema::create('credit_note_sequences', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->primary();
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();
        });

        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('sale_id')->constrained()->restrictOnDelete();
            $table->foreignId('sale_return_id')->unique()->constrained()->restrictOnDelete();
            $table->timestamp('issued_at');
            $table->char('currency', 3)->default('NIO');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->text('reason');
            $table->string('status')->default('issued');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['sale_id', 'issued_at']);
        });

        Schema::create('credit_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_note_id')->constrained()->restrictOnDelete();
            $table->foreignId('sale_detail_id')->constrained()->restrictOnDelete();
            $table->string('description');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->timestamps();
            $table->unique(['credit_note_id', 'sale_detail_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_note_items');
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('credit_note_sequences');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('sale_return_items');
        Schema::dropIfExists('sale_returns');
        Schema::dropIfExists('return_sequences');
    }
};
