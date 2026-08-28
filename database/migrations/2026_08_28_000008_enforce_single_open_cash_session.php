<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateRegister = DB::table('cash_sessions')
            ->where('status', 'open')
            ->groupBy('cash_register_id')
            ->havingRaw('COUNT(*) > 1')
            ->value('cash_register_id');
        $duplicateUser = DB::table('cash_sessions')
            ->where('status', 'open')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->value('user_id');
        if ($duplicateRegister || $duplicateUser) {
            throw new RuntimeException('Resolve duplicate open cash sessions before applying the unique-session constraints.');
        }

        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('open_register_guard')
                ->nullable()
                ->storedAs("CASE WHEN status = 'open' THEN cash_register_id ELSE NULL END");
            $table->unsignedBigInteger('open_user_guard')
                ->nullable()
                ->storedAs("CASE WHEN status = 'open' THEN user_id ELSE NULL END");
            $table->unique('open_register_guard');
            $table->unique('open_user_guard');
        });
    }

    public function down(): void
    {
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->dropUnique(['open_register_guard']);
            $table->dropUnique(['open_user_guard']);
            $table->dropColumn(['open_register_guard', 'open_user_guard']);
        });
    }
};
