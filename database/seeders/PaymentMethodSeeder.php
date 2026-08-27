<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentMethod::updateOrCreate(
            ['code' => 'CASH'],
            [
                'name' => 'Efectivo',
                'requires_reference' => false,
                'affects_cash_drawer' => true,
                'is_active' => true,
            ]
        );

        PaymentMethod::updateOrCreate(
            ['code' => 'OTHER'],
            [
                'name' => 'Otro',
                'requires_reference' => true,
                'affects_cash_drawer' => false,
                'is_active' => true,
            ]
        );
    }
}
