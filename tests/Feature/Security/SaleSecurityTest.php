<?php

use App\Models\AuditLog;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SalePayment;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryService;
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
        CashSession::forceCreate([
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
    app(InventoryService::class)->initialize($product, 5);
    $payment = PaymentMethod::create([
        'code' => 'CASH',
        'name' => 'Efectivo',
        'affects_cash_drawer' => true,
        'is_active' => true,
    ]);

    return compact('user', 'customer', 'product', 'payment');
}

function salesAdministrator(): User
{
    $role = Role::firstOrCreate(['name' => 'Administrador']);

    return User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
}

test('sale totals use database prices and inventory changes atomically', function () {
    $f = saleFixture();

    $sale = app(SaleService::class)->create([
        ['id' => $f['product']->id, 'quantity' => 2, 'price' => 0.01, 'subtotal' => 0.02],
    ], $f['customer']->id, $f['payment']->id, 60, $f['user']);

    expect((string) $sale->total)->toBe('51.00')
        ->and($f['product']->fresh()->stock)->toBe(3)
        ->and($sale->saleDetails)->toHaveCount(1)
        ->and((string) $sale->saleDetails->first()->price)->toBe('25.5000')
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

test('an administrator voids a sale with compensating stock and cash movements', function () {
    $f = saleFixture();
    $administrator = salesAdministrator();
    $this->actingAs($administrator);

    $sale = app(SaleService::class)->create([
        ['id' => $f['product']->id, 'quantity' => 2],
    ], $f['customer']->id, $f['payment']->id, 60, $f['user']);

    $voided = app(SaleService::class)->void($sale, 'Error comprobado en la operación de caja.', $administrator);

    expect($voided->status)->toBe('voided')
        ->and($voided->voided_by)->toBe($administrator->id)
        ->and($voided->voided_at)->not->toBeNull()
        ->and($f['product']->fresh()->stock)->toBe(5)
        ->and(Sale::count())->toBe(1)
        ->and(SaleDetail::count())->toBe(1)
        ->and(SalePayment::count())->toBe(1)
        ->and(StockMovement::count())->toBe(3)
        ->and((int) StockMovement::sum('quantity'))->toBe(5)
        ->and(CashMovement::count())->toBe(2)
        ->and(AuditLog::where('event', 'sale.voided')->where('user_id', $administrator->id)->count())->toBe(1);

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $f['product']->id,
        'type' => 'sale_void',
        'quantity' => 2,
        'stock_before' => 3,
        'stock_after' => 5,
    ]);
    $this->assertDatabaseHas('cash_movements', [
        'cash_session_id' => $sale->cash_session_id,
        'type' => 'refund',
        'amount' => 51,
        'reference_id' => $sale->id,
    ]);
});

test('a voided sale cannot be voided twice', function () {
    $f = saleFixture();
    $administrator = salesAdministrator();
    $this->actingAs($administrator);
    $service = app(SaleService::class);

    $sale = $service->create([
        ['id' => $f['product']->id, 'quantity' => 1],
    ], $f['customer']->id, $f['payment']->id, 30, $f['user']);

    $service->void($sale, 'Primera anulación válida de la venta.', $administrator);

    try {
        $service->void($sale, 'Segundo intento que debe rechazarse.', $administrator);
        $this->fail('Expected validation exception.');
    } catch (ValidationException) {
        expect($f['product']->fresh()->stock)->toBe(5)
            ->and(StockMovement::where('type', 'sale_void')->count())->toBe(1)
            ->and(CashMovement::where('type', 'refund')->count())->toBe(1)
            ->and(AuditLog::where('event', 'sale.voided')->count())->toBe(1);
    }
});

test('cash sale cannot be voided after its cash session is closed', function () {
    $f = saleFixture();
    $administrator = salesAdministrator();
    $this->actingAs($administrator);

    $sale = app(SaleService::class)->create([
        ['id' => $f['product']->id, 'quantity' => 1],
    ], $f['customer']->id, $f['payment']->id, 30, $f['user']);
    $sale->cashSession()->update([
        'status' => 'closed',
        'closing_amount' => 75.50,
        'expected_amount' => 75.50,
        'difference' => 0,
        'closed_at' => now(),
        'closed_by' => $administrator->id,
    ]);

    try {
        app(SaleService::class)->void($sale, 'Intento posterior al cierre de caja.', $administrator);
        $this->fail('Expected validation exception.');
    } catch (ValidationException) {
        expect($sale->fresh()->status)->toBe('completed')
            ->and($f['product']->fresh()->stock)->toBe(4)
            ->and(CashMovement::where('type', 'refund')->count())->toBe(0);
    }
});

test('accounted sales and their financial records cannot be deleted or edited', function () {
    $f = saleFixture();
    $sale = app(SaleService::class)->create([
        ['id' => $f['product']->id, 'quantity' => 1],
    ], $f['customer']->id, $f['payment']->id, 30, $f['user']);

    expect(fn () => $sale->delete())->toThrow(LogicException::class)
        ->and(fn () => $sale->saleDetails()->first()->delete())->toThrow(LogicException::class)
        ->and(fn () => $sale->salePayments()->first()->update(['amount' => 0]))->toThrow(LogicException::class)
        ->and(fn () => $sale->update(['total' => 0]))->toThrow(LogicException::class);

    expect(Sale::count())->toBe(1)
        ->and(SaleDetail::count())->toBe(1)
        ->and(SalePayment::count())->toBe(1);
});
