<?php

use App\Livewire\CashPanel;
use App\Livewire\DashboardPanel;
use App\Livewire\InvoicingPanel;
use App\Livewire\ReportsPanel;
use App\Livewire\SalesHistoryPanel;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('financial workspace renders its accounting surfaces for an administrator', function () {
    $role = Role::create(['name' => 'Administrador']);
    $admin = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);

    Livewire::actingAs($admin)->test(DashboardPanel::class)
        ->assertSee('Panorama financiero')
        ->assertSee('Venta bruta vigente')
        ->assertSee('Operación neta de hoy');

    Livewire::actingAs($admin)->test(CashPanel::class)
        ->assertSee('Caja y arqueo')
        ->assertSee('Apertura de caja');

    Livewire::actingAs($admin)->test(InvoicingPanel::class)
        ->assertSee('Panel de control: Facturación')
        ->assertSee('Datos de cliente')
        ->assertSee('Método de pago');

    Livewire::actingAs($admin)->test(SalesHistoryPanel::class)
        ->assertSee('Libro de ventas')
        ->assertSee('Venta neta');

    Livewire::actingAs($admin)->test(ReportsPanel::class)
        ->assertSee('Informes y exportación')
        ->assertSee('Centro documental')
        ->assertSee('Libro de ventas');
});

test('financial routes load inside the responsive accounting shell', function () {
    $role = Role::create(['name' => 'Administrador']);
    $admin = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);

    foreach (['dashboard', 'cash', 'invoicing', 'salesHistory', 'reports'] as $route) {
        $this->actingAs($admin)->get(route($route))
            ->assertOk()
            ->assertSee('Main Menu')
            ->assertSee('Dashboard');
    }
});
