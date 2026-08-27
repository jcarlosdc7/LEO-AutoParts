<?php

use App\Http\Controllers\InvoiceController;
use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\RoleMiddleware;
use App\Livewire\CashPanel;
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

Route::middleware(['auth', 'verified', EnsureActiveUser::class])->group(function () {
    Route::get('dashboard', DashboardPanel::class)->name('dashboard')->lazy();
    Route::get('catalog', CatalogPanel::class)->name('catalog')->lazy();
    Route::get('invoices/{sale}/download', [InvoiceController::class, 'download'])
        ->name('invoices.download');

    Route::middleware(RoleMiddleware::class . ':Administrador')->group(function () {
        Route::get('users', UsersPanel::class)->name('users')->lazy();
        Route::get('configuration', ConfigurationPanel::class)->name('configuration')->lazy();
    });

    Route::middleware(RoleMiddleware::class . ':Administrador,Contador')->group(function () {
        Route::get('reports', ReportsPanel::class)->name('reports')->lazy();
        Route::get('inventory', InventoryPanel::class)->name('inventory')->lazy();
        Route::get('salesHistory', SalesHistoryPanel::class)->name('salesHistory')->lazy();
        Route::get('suppliers', SuppliersPanel::class)->name('suppliers')->lazy();
    });

    Route::middleware(RoleMiddleware::class . ':Administrador,Vendedor')->group(function () {
        Route::get('cash', CashPanel::class)->name('cash')->lazy();
        Route::get('customers', CustomersPanel::class)->name('customers')->lazy();
        Route::get('invoicing', InvoicingPanel::class)->name('invoicing')->lazy();
    });
});

Route::view('profile', 'profile')
    ->middleware(['auth', EnsureActiveUser::class])
    ->name('profile');

require __DIR__.'/auth.php';