<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		$user = new User();
		$user->name = 'José Carlos Dávila';
		$user->email = 'admin@email.com';
		$user->password = Hash::make('password');
		$user->role_id = 1;
		$user->save();

		$user = new User();
		$user->name = 'Valeska Herrera';
		$user->email = 'cont@email.com';
		$user->password = Hash::make('password');
		$user->role_id = 2;
		$user->save();

		$user = new User();
		$user->name = 'Joshua Valle';
		$user->email = 'vend@email.com';
		$user->password = Hash::make('password');
		$user->role_id = 3;
		$user->save();
	}
}
