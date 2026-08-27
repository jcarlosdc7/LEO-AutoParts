<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('SEED_DEFAULT_PASSWORD');

        if (! $password && ! app()->environment('testing')) {
            throw new RuntimeException('Defina SEED_DEFAULT_PASSWORD antes de crear usuarios iniciales.');
        }

        $password ??= 'testing-only-password';
        $users = [
            ['name' => 'José Carlos Dávila', 'email' => 'admin@email.com', 'role_id' => 1],
            ['name' => 'Valeska Herrera', 'email' => 'cont@email.com', 'role_id' => 2],
            ['name' => 'Joshua Valle', 'email' => 'vend@email.com', 'role_id' => 3],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                $data + ['password' => Hash::make($password), 'is_active' => true],
            );
        }
    }
}
