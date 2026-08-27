<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('code', 32)->nullable()->after('id');
            $table->boolean('requires_reference')->default(false)->after('description');
            $table->boolean('affects_cash_drawer')->default(false)->after('requires_reference');
            $table->boolean('is_active')->default(true)->after('affects_cash_drawer');
        });

        DB::table('payment_methods')->orderBy('id')->get()->each(function (object $method): void {
            $normalizedName = Str::upper(Str::ascii($method->name));
            $code = match ($normalizedName) {
                'EFECTIVO', 'CASH' => 'CASH',
                'TARJETA', 'CARD' => 'CARD',
                'TRANSFERENCIA', 'TRANSFER' => 'TRANSFER',
                'CREDITO', 'CREDIT' => 'CREDIT',
                default => 'LEGACY_'.$method->id,
            };

            DB::table('payment_methods')->where('id', $method->id)->update([
                'code' => $code,
                'requires_reference' => in_array($code, ['CARD', 'TRANSFER'], true),
                'affects_cash_drawer' => $code === 'CASH',
            ]);
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('code', 32)->nullable(false)->change();
            $table->unique('code');
        });
    }

    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn(['code', 'requires_reference', 'affects_cash_drawer', 'is_active']);
        });
    }
};
