<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            $table->string('currency_code', 3)->default('NIO')->after('code');
        });

        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->uuid('opening_operation_id')->nullable()->unique()->after('user_id');
            $table->uuid('closing_operation_id')->nullable()->unique()->after('opening_operation_id');
        });

        Schema::table('cash_movements', function (Blueprint $table) {
            $table->uuid('operation_id')->nullable()->unique()->after('user_id');
            $table->string('reference')->nullable()->after('reason');
            $table->foreignId('approved_by')->nullable()->after('reference')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->unique(['reference_type', 'reference_id', 'type'], 'cash_movement_reference_type_unique');
        });

        Schema::create('cash_denominations', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code', 3);
            $table->decimal('value', 12, 2);
            $table->string('type', 16);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['currency_code', 'value', 'type']);
            $table->index(['currency_code', 'is_active', 'sort_order'], 'cash_denomination_lookup');
        });

        Schema::create('cash_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_session_id')->constrained()->restrictOnDelete();
            $table->uuid('operation_id')->unique();
            $table->string('type', 16);
            $table->decimal('total', 12, 2);
            $table->decimal('expected_amount', 12, 2)->nullable();
            $table->decimal('difference', 12, 2)->nullable();
            $table->text('difference_reason')->nullable();
            $table->foreignId('performed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('performed_at');
            $table->timestamps();
            $table->unique(['cash_session_id', 'type']);
        });

        Schema::create('cash_count_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_count_id')->constrained()->restrictOnDelete();
            $table->foreignId('cash_denomination_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
            $table->unique(['cash_count_id', 'cash_denomination_id'], 'cash_count_denomination_unique');
        });

        $now = now();
        $rows = [
            ['1000.00', 'BANKNOTE'], ['500.00', 'BANKNOTE'], ['200.00', 'BANKNOTE'],
            ['100.00', 'BANKNOTE'], ['50.00', 'BANKNOTE'], ['20.00', 'BANKNOTE'], ['10.00', 'BANKNOTE'],
            ['10.00', 'COIN'], ['5.00', 'COIN'], ['1.00', 'COIN'], ['0.50', 'COIN'], ['0.25', 'COIN'],
        ];
        foreach ($rows as $index => [$value, $type]) {
            DB::table('cash_denominations')->updateOrInsert(
                ['currency_code' => 'NIO', 'value' => $value, 'type' => $type],
                ['is_active' => true, 'sort_order' => $index + 1, 'created_at' => $now, 'updated_at' => $now],
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_count_lines');
        Schema::dropIfExists('cash_counts');
        Schema::dropIfExists('cash_denominations');

        Schema::table('cash_movements', function (Blueprint $table) {
            $table->dropUnique('cash_movement_reference_type_unique');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropUnique(['operation_id']);
            $table->dropColumn(['operation_id', 'reference', 'approved_at']);
        });

        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->dropUnique(['opening_operation_id']);
            $table->dropUnique(['closing_operation_id']);
            $table->dropColumn(['opening_operation_id', 'closing_operation_id']);
        });

        Schema::table('cash_registers', fn (Blueprint $table) => $table->dropColumn('currency_code'));
    }
};
