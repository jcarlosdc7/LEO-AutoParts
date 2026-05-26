<?php

namespace Database\Seeders;

use App\Models\CustomerType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerTypeSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		$customertype = new CustomerType();
		$customertype->name = 'General';
		$customertype->save();

		$customertype = new CustomerType();
		$customertype->name = 'Minorista';
		$customertype->save();

		$customertype = new CustomerType();
		$customertype->name = 'Mayorista';
		$customertype->save();
	}
}
