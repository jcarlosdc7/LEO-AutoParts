<?php

use App\Models\AuditLog;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\Category;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Role;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\ReturnService;
use App\Services\SaleService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

function returnFixture(bool $adminCash = true, string $adminOpening = '1000.00'): array
{
    $vendorRole = Role::firstOrCreate(['name' => 'Vendedor']);
    $adminRole = Role::firstOrCreate(['name' => 'Administrador']);
    $vendor = User::factory()->create(['role_id' => $vendorRole->id, 'is_active' => true]);
    $admin = User::factory()->create(['role_id' => $adminRole->id, 'is_active' => true]);
    $register = CashRegister::create(['name' => 'Caja devolución', 'code' => 'RET-01', 'is_active' => true]);
    CashSession::create(['cash_register_id' => $register->id, 'user_id' => $vendor->id, 'opening_amount' => 100, 'status' => 'open', 'opened_at' => now()]);
    if ($adminCash) {
        $adminRegister = CashRegister::create(['name' => 'Caja devolución administrativa', 'code' => 'RET-ADMIN', 'is_active' => true]);
        CashSession::create(['cash_register_id' => $adminRegister->id, 'user_id' => $admin->id, 'opening_amount' => $adminOpening, 'status' => 'open', 'opened_at' => now()]);
    }
    $customerType = CustomerType::create(['name' => 'General']);
    $customer = Customer::create(['dni_ruc' => 'RET-001', 'name' => 'Cliente', 'address' => 'Managua', 'city' => 'Managua', 'customer_type_id' => $customerType->id, 'is_active' => true]);
    $supplier = Supplier::create(['name' => 'Proveedor', 'is_active' => true]);
    $category = Category::create(['name' => 'Categoría']);
    $product = Product::create(['code' => 'RET-P1', 'name' => 'Pieza', 'brand' => 'Marca', 'model' => 'M1', 'supplier_id' => $supplier->id, 'category_id' => $category->id, 'stock' => 10, 'min_stock' => 1, 'price' => '10.10', 'is_active' => true]);
    app(InventoryService::class)->initialize($product, 10);
    $cash = PaymentMethod::create(['code' => 'CASH', 'name' => 'Efectivo', 'affects_cash_drawer' => true, 'is_active' => true]);
    $transfer = PaymentMethod::create(['code' => 'TRANSFER', 'name' => 'Transferencia', 'requires_reference' => true, 'affects_cash_drawer' => false, 'is_active' => true]);
    $sale = app(SaleService::class)->create([['id' => $product->id, 'quantity' => 5]], $customer->id, $cash->id, 60, $vendor);

    return compact('vendor', 'admin', 'product', 'cash', 'transfer', 'sale');
}

function processReturn(array $f, int $quantity, bool $restock = true, ?string $operationId = null, ?PaymentMethod $method = null): SaleReturn
{
    return app(ReturnService::class)->process(
        $f['sale'],
        [['sale_detail_id' => $f['sale']->saleDetails()->first()->id, 'quantity' => $quantity, 'restock' => $restock, 'condition' => $restock ? 'sellable' : 'defective']],
        ($method ?? $f['cash'])->id,
        'Devolución autorizada por defecto comprobado.',
        $f['admin'],
        $operationId ?? (string) Str::uuid(),
        ($method ?? $f['cash'])->requires_reference ? 'TRX-123' : null,
    );
}

test('partial cash return preserves sale and issues stock refund and credit note', function () {
    $f = returnFixture();
    $original = $f['sale']->only(['total', 'amount', 'change']);
    $return = processReturn($f, 2);

    expect($return->return_number)->toMatch('/^DEV-\d{4}-\d{6}$/')
        ->and($return->refund->amount)->toBe('20.20')
        ->and($return->creditNote->number)->toMatch('/^NC-\d{4}-\d{6}$/')
        ->and($return->creditNote->total)->toBe('20.20')
        ->and($f['product']->fresh()->stock)->toBe(7)
        ->and($f['sale']->fresh()->only(['total', 'amount', 'change']))->toBe($original)
        ->and(CashMovement::where('reference_type', Refund::class)->count())->toBe(1)
        ->and(StockMovement::where('type', 'customer_return')->count())->toBe(1)
        ->and(AuditLog::where('event', 'return.completed')->count())->toBe(1)
        ->and(AuditLog::whereIn('event', ['return.completed', 'refund.created', 'credit_note.issued'])->count())->toBe(3);
});

test('multiple returns can consume exactly the original quantity', function () {
    $f = returnFixture();
    processReturn($f, 2);
    processReturn($f, 3);

    expect(SaleReturn::count())->toBe(2)
        ->and((int) SaleReturnItem::sum('quantity'))->toBe(5)
        ->and(Refund::sum('amount'))->toEqual(50.50)
        ->and($f['product']->fresh()->stock)->toBe(10)
        ->and($f['sale']->fresh()->net_economic_value)->toBe('0.00');
    expect(SaleReturn::pluck('return_number')->unique())->toHaveCount(2)
        ->and(CreditNote::pluck('number')->unique())->toHaveCount(2);
});

test('serialized return limit prevents a second operation consuming returned units', function () {
    $f = returnFixture();
    processReturn($f, 5);

    expect(fn () => processReturn($f, 1))->toThrow(ValidationException::class)
        ->and((int) SaleReturnItem::sum('quantity'))->toBe(5)
        ->and(SaleReturn::count())->toBe(1)
        ->and(Refund::count())->toBe(1);
});

test('over return and invalid quantities roll back every effect', function (int $quantity) {
    $f = returnFixture();
    try {
        processReturn($f, $quantity);
        $this->fail('Expected validation exception.');
    } catch (ValidationException) {
        expect(SaleReturn::count())->toBe(0)->and(Refund::count())->toBe(0)
            ->and(CreditNote::count())->toBe(0)->and($f['product']->fresh()->stock)->toBe(5);
    }
})->with([0, -1, 6]);

test('non cash refund with no restock requires reference and does not affect cash', function () {
    $f = returnFixture();
    $before = CashMovement::count();
    $return = processReturn($f, 1, false, null, $f['transfer']);

    expect($return->refund->paymentMethod->code)->toBe('TRANSFER')
        ->and($return->refund->cash_session_id)->toBeNull()
        ->and($return->refund->reference)->toBe('TRX-123')
        ->and(CashMovement::count())->toBe($before)
        ->and($f['product']->fresh()->stock)->toBe(5);
});

test('cash refund requires active cash and sufficient balance', function (bool $active, string $opening) {
    $f = returnFixture($active, $opening);
    try {
        processReturn($f, 1);
        $this->fail('Expected validation exception.');
    } catch (ValidationException) {
        expect(SaleReturn::count())->toBe(0)->and($f['product']->fresh()->stock)->toBe(5);
    }
})->with([[false, '0.00'], [true, '5.00']]);

test('operation id makes retries idempotent', function () {
    $f = returnFixture();
    $key = (string) Str::uuid();
    $first = processReturn($f, 1, true, $key);
    $second = processReturn($f, 1, true, $key);

    expect($second->id)->toBe($first->id)
        ->and(SaleReturn::count())->toBe(1)->and(Refund::count())->toBe(1)
        ->and(CreditNote::count())->toBe(1)->and($f['product']->fresh()->stock)->toBe(6);
});

test('voided sales and unauthorized users cannot create returns', function () {
    $f = returnFixture();
    expect(fn () => app(ReturnService::class)->process($f['sale'], [['sale_detail_id' => $f['sale']->saleDetails()->first()->id, 'quantity' => 1, 'restock' => true]], $f['cash']->id, 'Motivo suficientemente detallado.', $f['vendor'], (string) Str::uuid()))->toThrow(HttpException::class);

    app(SaleService::class)->void($f['sale'], 'Anulación válida antes de devolución.', $f['admin']);
    expect(fn () => processReturn($f, 1))->toThrow(ValidationException::class);
});

test('inactive users invalid reasons unknown sales and invalid conditions are rejected', function () {
    $f = returnFixture();
    $detailId = $f['sale']->saleDetails()->first()->id;
    $f['admin']->update(['is_active' => false]);

    expect(fn () => processReturn($f, 1))->toThrow(HttpException::class);
    $f['admin']->update(['is_active' => true]);

    $service = app(ReturnService::class);
    expect(fn () => $service->process($f['sale'], [['sale_detail_id' => $detailId, 'quantity' => 1]], $f['cash']->id, 'corto', $f['admin'], (string) Str::uuid()))->toThrow(ValidationException::class)
        ->and(fn () => $service->process(999999, [['sale_detail_id' => $detailId, 'quantity' => 1]], $f['cash']->id, 'Motivo suficientemente detallado.', $f['admin'], (string) Str::uuid()))->toThrow(ModelNotFoundException::class)
        ->and(fn () => $service->process($f['sale'], [['sale_detail_id' => $detailId, 'quantity' => 1, 'condition' => 'invented']], $f['cash']->id, 'Motivo suficientemente detallado.', $f['admin'], (string) Str::uuid()))->toThrow(ValidationException::class)
        ->and(SaleReturn::count())->toBe(0);
});

test('return keeps original line and payment records unchanged', function () {
    $f = returnFixture();
    $detail = $f['sale']->saleDetails()->first();
    $payment = $f['sale']->salePayments()->first();
    $detailSnapshot = $detail->getAttributes();
    $paymentSnapshot = $payment->getAttributes();

    processReturn($f, 2);

    expect($detail->fresh()->getAttributes())->toBe($detailSnapshot)
        ->and($payment->fresh()->getAttributes())->toBe($paymentSnapshot);
});

test('completed return refund and credit note are immutable', function () {
    $f = returnFixture();
    $return = processReturn($f, 1);

    expect(fn () => $return->delete())->toThrow(LogicException::class)
        ->and(fn () => $return->items()->first()->update(['quantity' => 2]))->toThrow(LogicException::class)
        ->and(fn () => $return->refund->update(['amount' => 0]))->toThrow(LogicException::class)
        ->and(fn () => $return->creditNote->delete())->toThrow(LogicException::class);
});

test('failure while issuing credit note rolls back inventory refund and return', function () {
    $f = returnFixture();
    $dummyReturn = SaleReturn::create([
        'operation_id' => (string) Str::uuid(), 'sale_id' => $f['sale']->id, 'return_number' => 'DEV-DUMMY',
        'status' => 'completed', 'reason' => 'Documento reservado para forzar colisión.',
        'requested_by' => $f['admin']->id, 'authorized_by' => $f['admin']->id, 'completed_at' => now(),
    ]);
    CreditNote::create([
        'number' => 'NC-'.now()->format('Y').'-000001', 'sale_id' => $f['sale']->id,
        'sale_return_id' => $dummyReturn->id, 'issued_at' => now(), 'currency' => 'NIO',
        'subtotal' => '0.00', 'tax' => '0.00', 'total' => '0.00', 'reason' => 'Reserva de prueba',
        'status' => 'issued', 'created_by' => $f['admin']->id,
    ]);

    expect(fn () => processReturn($f, 1))->toThrow(UniqueConstraintViolationException::class)
        ->and(SaleReturn::count())->toBe(1)
        ->and(Refund::count())->toBe(0)
        ->and(CreditNote::count())->toBe(1)
        ->and(StockMovement::where('type', 'customer_return')->count())->toBe(0)
        ->and(CashMovement::where('reference_type', Refund::class)->count())->toBe(0)
        ->and($f['product']->fresh()->stock)->toBe(5);
});
