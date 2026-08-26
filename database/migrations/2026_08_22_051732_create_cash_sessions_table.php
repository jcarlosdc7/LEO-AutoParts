<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opened_by')->constrained('users')->restrictOnDelete(); $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('opening_amount', 12, 2); $table->decimal('expected_amount', 12, 2)->nullable();
            $table->decimal('closing_amount', 12, 2)->nullable(); $table->decimal('difference', 12, 2)->nullable();
            $table->timestamp('opened_at'); $table->timestamp('closed_at')->nullable(); $table->string('status')->default('open'); $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_sessions');
    }
};
