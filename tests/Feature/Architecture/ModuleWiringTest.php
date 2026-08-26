<?php

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
use Illuminate\Support\Facades\Route;

it('connects every business route to its modular Livewire page', function () {
    $routes = [
        'dashboard' => DashboardPage::class,
        'users' => UsersPage::class,
        'configuration' => ConfigurationPage::class,
        'reports' => ReportsPage::class,
        'inventory' => InventoryPage::class,
        'purchases' => PurchasesPage::class,
        'salesHistory' => SalesHistoryPage::class,
        'suppliers' => SuppliersPage::class,
        'kardex' => KardexPage::class,
        'cash' => CashPage::class,
        'catalog' => CatalogPage::class,
        'customers' => CustomersPage::class,
        'invoicing' => InvoicingPage::class,
    ];

    foreach ($routes as $name => $page) {
        expect(Route::getRoutes()->getByName($name)?->getActionName())->toBe($page);
    }
});

it('provides a Blade view for every business module', function () {
    $views = [
        'livewire.dashboard.index',
        'livewire.administration.users',
        'livewire.administration.configuration',
        'livewire.reports.index',
        'livewire.inventory.index',
        'livewire.inventory.catalog',
        'livewire.inventory.kardex',
        'livewire.purchases.index',
        'livewire.sales.invoicing',
        'livewire.sales.history',
        'livewire.suppliers.index',
        'livewire.cash.index',
        'livewire.customers.index',
    ];

    foreach ($views as $view) {
        expect(view()->exists($view))->toBeTrue();
    }
});

it('keeps every visible report download connected to a page action', function () {
    $actions = [
        'downloadSalesExcel',
        'downloadSalesPdf',
        'downloadProductsExcel',
        'downloadProductsPdf',
        'downloadCustomersExcel',
        'downloadCustomersPdf',
        'downloadUsersExcel',
        'downloadUsersPdf',
        'downloadPaymentsExcel',
        'downloadPaymentsPdf',
        'downloadStockExcel',
        'downloadStockPdf',
    ];

    foreach ($actions as $action) {
        expect(method_exists(ReportsPage::class, $action))->toBeTrue();
    }
});
