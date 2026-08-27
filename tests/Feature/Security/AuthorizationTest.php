<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function userWithRole(string $role): User
{
    $model = Role::firstOrCreate(['name' => $role]);

    return User::factory()->create(['role_id' => $model->id, 'is_active' => true]);
}

test('accountants cannot access invoicing or user administration', function () {
    $this->actingAs(userWithRole('Contador'));

    $this->get('/invoicing')->assertForbidden();
    $this->get('/users')->assertForbidden();
    $this->get('/reports')->assertOk();
});

test('vendors cannot access administrative reports', function () {
    $this->actingAs(userWithRole('Vendedor'));

    $this->get('/reports')->assertForbidden();
    $this->get('/users')->assertForbidden();
});

test('inactive users are logged out and denied access', function () {
    $user = userWithRole('Administrador');
    $user->update(['is_active' => false]);

    $this->actingAs($user)->get('/dashboard')->assertRedirect('/login');
    $this->assertGuest();
});
