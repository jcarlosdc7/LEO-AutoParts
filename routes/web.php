<?php

use App\Http\Middleware\RoleMiddleware;
use App\Livewire\Administration\ConfigurationPage;
use App\Livewire\Administration\UsersPage;
use App\Livewire\Cash\CashPage;
use App\Livewire\Customers\CustomersPage;
use App\Livewire\Dashboard\DashboardPage;
use App\Livewire\Inventory\CatalogPage;
use App\Livewire\Inventory\InventoryPage;
use App\Livewire\Inventory\KardexPage;
use App\Livewire\Purchases\PurchasesPage;
use App\Livewire\Reports\ReportsPage;
use App\Livewire\Sales\InvoicingPage;
use App\Livewire\Sales\SalesHistoryPage;
use App\Livewire\Suppliers\SuppliersPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardPage::class)->name('dashboard')->lazy();

    Route::middleware(RoleMiddleware::class.':Administrador')->group(function () {
        Route::get('users', UsersPage::class)->name('users')->lazy();
        Route::get('configuration', ConfigurationPage::class)->name('configuration')->lazy();
    });

    Route::middleware(RoleMiddleware::class.':Administrador,Contador')->group(function () {
        Route::get('reports', ReportsPage::class)->name('reports')->lazy();
        Route::get('inventory', InventoryPage::class)->name('inventory')->lazy();
        Route::get('purchases', PurchasesPage::class)->name('purchases')->lazy();
        Route::get('sales-history', SalesHistoryPage::class)->name('salesHistory')->lazy();
        Route::get('suppliers', SuppliersPage::class)->name('suppliers')->lazy();
        Route::get('kardex', KardexPage::class)->name('kardex')->lazy();
        Route::get('cash', CashPage::class)->name('cash')->lazy();
    });

    Route::get('catalog', CatalogPage::class)->name('catalog')->lazy();
    Route::get('customers', CustomersPage::class)->name('customers')->lazy();
    Route::get('invoicing', InvoicingPage::class)->name('invoicing')->lazy();
});

Route::post('logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('logout');

Route::view('profile', 'profile')
    ->middleware('auth')
    ->name('profile');

require __DIR__.'/auth.php';
