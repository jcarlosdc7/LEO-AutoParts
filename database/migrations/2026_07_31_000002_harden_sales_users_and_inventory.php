<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('role_id');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('price');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('cash_session_id')->nullable()->after('user_id')->constrained()->restrictOnDelete();
            $table->string('status')->default('completed')->after('payment_method_id')->index();
            $table->text('void_reason')->nullable()->after('status');
            $table->foreignId('voided_by')->nullable()->after('void_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable()->after('voided_by');
        });

        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_method_id')->constrained()->restrictOnDelete();
            $table->foreignId('cash_session_id')->nullable()->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('received_amount', 12, 2)->nullable();
            $table->decimal('change_amount', 12, 2)->default(0);
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        DB::table('cash_registers')->updateOrInsert(
            ['code' => 'CAJA-01'],
            ['name' => 'Caja principal', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cash_session_id');
            $table->dropConstrainedForeignId('voided_by');
            $table->dropColumn(['status', 'void_reason', 'voided_at']);
        });
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn('is_active'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['is_active', 'last_login_at']));
    }
};
