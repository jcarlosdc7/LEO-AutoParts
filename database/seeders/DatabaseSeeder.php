<?php

namespace Database\Seeders;

use App\Models\CustomerType;
use App\Models\PaymentMethod;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	/**
	 * Seed the application's database.
	 */
	public function run(): void
	{
		// User::factory(10)->create();

		// User::factory()->create([
		//     'name' => 'Test User',
		//     'email' => 'test@example.com',
		// ]);

		$this->call(RoleSeeder::class);
		$this->call(UserSeeder::class);
		$this->call(SupplierSeeder::class);
		$this->call(CategorySeeder::class);
		$this->call(CustomerTypeSeeder::class);
		$this->call(CustomerSeeder::class);
		$this->call(ProductSeeder::class);
		$this->call(PaymentMethodSeeder::class);
		$this->call(SaleSeeder::class);
	}
}
