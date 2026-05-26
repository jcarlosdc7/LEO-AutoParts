<?php

use App\Http\Middleware\RoleMiddleware;
use App\Livewire\CatalogPanel;
use App\Livewire\ConfigurationPanel;
use App\Livewire\CustomersPanel;
use App\Livewire\DashboardPanel;
use App\Livewire\InventoryPanel;
use App\Livewire\InvoicingPanel;
use App\Livewire\ReportsPanel;
use App\Livewire\SalesHistoryPanel;
use App\Livewire\SuppliersPanel;
use App\Livewire\UsersPanel;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'dashboard');

//Route::get('g', )->name('g')->lazy();

//==============================SIDEBAR MENU ROUTES===============================

Route::middleware(['auth', 'verified'])->group(function () {
	Route::get('dashboard', DashboardPanel::class)->name('dashboard')->lazy();

	// Solo 'Administrador' puede acceder a Usuarios y Configuración
	Route::middleware([RoleMiddleware::class . ':Administrador'])->group(function () {
		Route::get('users', UsersPanel::class)->name('users')->lazy();
		Route::get('configuration', ConfigurationPanel::class)->name('configuration')->lazy();
	});

	// 'Administrador' y 'Contador' pueden acceder a Reportes e Inventario
	Route::middleware([RoleMiddleware::class . ':Administrador,Contador'])->group(function () {
		Route::get('reports', ReportsPanel::class)->name('reports')->lazy();
		Route::get('inventory', InventoryPanel::class)->name('inventory')->lazy();
		Route::get('salesHistory', SalesHistoryPanel::class)->name('salesHistory')->lazy();
		Route::get('suppliers', SuppliersPanel::class)->name('suppliers')->lazy();
	});

	// Acceso para todos los roles autenticados
	Route::get('catalog', CatalogPanel::class)->name('catalog')->lazy();
	Route::get('customers', CustomersPanel::class)->name('customers')->lazy();
	Route::get('invoicing', InvoicingPanel::class)->name('invoicing')->lazy();
	
});

// Route::middleware(['auth', 'verified'])->group(function() {
// 	Route::get('dashboard', DashboardPanel::class)->name('dashboard')->lazy();
// });

// Route::middleware(['auth', 'verified'])->group(function() {
// 	Route::get('catalog', CatalogPanel::class)->name('catalog')->lazy();
// });

// Route::middleware(['auth', 'verified'])->group(function() {
// 	Route::get('customers', CustomersPanel::class)->name('customers')->lazy();
// });

// Route::middleware(['auth', 'verified'])->group(function() {
// 	Route::get('invoicing', InvoicingPanel::class)->name('invoicing')->lazy();
// });

// Route::middleware(['auth', 'verified'])->group(function() {
// 	Route::get('salesHistory', SalesHistoryPanel::class)->name('salesHistory')->lazy();
// });

// Route::middleware(['auth', 'verified'])->group(function() {
// 	Route::get('inventory', InventoryPanel::class)->name('inventory')->lazy();
// });

// Route::middleware(['auth', 'verified'])->group(function() {
// 	Route::get('suppliers', SuppliersPanel::class)->name('suppliers')->lazy();
// });

// Route::middleware(['auth', 'verified'])->group(function() {
// 	Route::get('reports', ReportsPanel::class)->name('reports')->lazy();
// });

// Route::middleware([RoleMiddleware::class . ':Administrador'])->group(function() {
// 	Route::get('users', UsersPanel::class)->name('users')->lazy();
// });

// Route::middleware([RoleMiddleware::class . ':Administrador'])->group(function() {
// 	Route::get('configuration', ConfigurationPanel::class)->name('configuration')->lazy();
// });

//=============================================================

Route::view('profile', 'profile')
	->middleware(['auth'])
	->name('profile');

require __DIR__.'/auth.php';
