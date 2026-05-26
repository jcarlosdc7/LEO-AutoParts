<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		$paymentMethod = new PaymentMethod();
		$paymentMethod->name = 'Efectivo';
		$paymentMethod->save();

		$paymentMethod = new PaymentMethod();
		$paymentMethod->name = 'PayPal';
		$paymentMethod->save();
	}
}
