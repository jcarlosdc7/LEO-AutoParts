<?php

use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function saleFixture(bool $openCash = true): array
{
    $role = Role::create(['name' => 'Vendedor']);
    $user = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
    $register = CashRegister::create(['name' => 'Caja prueba', 'code' => 'TEST-01', 'is_active' => true]);
    if ($openCash) {
        CashSession::create([
            'cash_register_id' => $register->id,
            'user_id' => $user->id,
            'opening_amount' => 50,
            'status' => 'open',
            'opened_at' => now(),
        ]);
    }
    $type = CustomerType::create(['name' => 'General']);
    $customer = Customer::create([
        'dni_ruc' => 'TEST-001', 'name' => 'Cliente prueba', 'address' => 'Managua',
        'city' => 'Managua', 'customer_type_id' => $type->id, 'is_active' => true,
    ]);
    $supplier = Supplier::create(['name' => 'Proveedor prueba', 'is_active' => true]);
    $category = Category::create(['name' => 'Categoría prueba']);
    $product = Product::create([
        'code' => 'P-001', 'name' => 'Producto prueba', 'brand' => 'Marca', 'model' => 'M1',
        'supplier_id' => $supplier->id, 'category_id' => $category->id,
        'stock' => 5, 'min_stock' => 1, 'price' => 25.50, 'is_active' => true,
    ]);
    $payment = PaymentMethod::create(['name' => 'Efectivo']);

    return compact('user', 'customer', 'product', 'payment');
}

test('sale totals use database prices and inventory changes atomically', function () {
    $f = saleFixture();

    $sale = app(SaleService::class)->create([
        ['id' => $f['product']->id, 'quantity' => 2, 'price' => 0.01, 'subtotal' => 0.02],
    ], $f['customer']->id, $f['payment']->id, 60, $f['user']);

    expect((float) $sale->total)->toBe(51.0)
        ->and($f['product']->fresh()->stock)->toBe(3)
        ->and($sale->saleDetails)->toHaveCount(1)
        ->and((float) $sale->saleDetails->first()->price)->toBe(25.5)
        ->and($sale->salePayments)->toHaveCount(1);

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $f['product']->id, 'quantity' => -2, 'stock_before' => 5, 'stock_after' => 3,
    ]);
    $this->assertDatabaseHas('cash_movements', ['cash_session_id' => $sale->cash_session_id, 'amount' => 51]);
});

test('insufficient stock rolls back the complete sale', function () {
    $f = saleFixture();

    try {
        app(SaleService::class)->create([
            ['id' => $f['product']->id, 'quantity' => 6],
        ], $f['customer']->id, $f['payment']->id, 200, $f['user']);
        $this->fail('Expected validation exception.');
    } catch (ValidationException) {
        expect(Sale::count())->toBe(0)
            ->and($f['product']->fresh()->stock)->toBe(5);
    }
});

test('a sale cannot be recorded without an open cash session', function () {
    $f = saleFixture(false);

    $this->expectException(ValidationException::class);
    app(SaleService::class)->create([
        ['id' => $f['product']->id, 'quantity' => 1],
    ], $f['customer']->id, $f['payment']->id, 30, $f['user']);
});