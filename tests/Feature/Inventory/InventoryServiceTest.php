<?php

use App\Livewire\InventoryPanel;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\InventoryAdjustment;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

function inventoryFixture(): array
{
    $adminRole = Role::firstOrCreate(['name' => 'Administrador']);
    $accountantRole = Role::firstOrCreate(['name' => 'Contador']);
    $vendorRole = Role::firstOrCreate(['name' => 'Vendedor']);
    $admin = User::factory()->create(['role_id' => $adminRole->id, 'is_active' => true]);
    $accountant = User::factory()->create(['role_id' => $accountantRole->id, 'is_active' => true]);
    $vendor = User::factory()->create(['role_id' => $vendorRole->id, 'is_active' => true]);
    $supplier = Supplier::create(['name' => 'Proveedor inventario', 'is_active' => true]);
    $category = Category::create(['name' => 'Categoría inventario']);
    $product = Product::create([
        'code' => 'INV-001',
        'name' => 'Producto inventario',
        'description' => 'Producto para pruebas de ledger.',
        'brand' => 'Marca',
        'model' => 'Modelo',
        'supplier_id' => $supplier->id,
        'category_id' => $category->id,
        'stock' => 999,
        'min_stock' => 2,
        'price' => '15.25',
        'is_active' => true,
    ]);
    app(InventoryService::class)->initialize($product, 5, $admin);
    $product->refresh();

    return compact('admin', 'accountant', 'vendor', 'supplier', 'category', 'product');
}

test('adjustments are atomic idempotent auditable and reconciliable', function () {
    $fixture = inventoryFixture();
    $service = app(InventoryService::class);
    $operationId = (string) Str::uuid();
    $first = $service->adjust(
        $fixture['product']->id,
        'increase',
        3,
        'Entrada administrativa correctamente justificada.',
        $fixture['admin'],
        $operationId,
    );
    $retry = $service->adjust(
        $fixture['product']->id,
        'increase',
        3,
        'Entrada administrativa correctamente justificada.',
        $fixture['admin'],
        $operationId,
    );
    $service->adjust(
        $fixture['product']->id,
        'count',
        6,
        'Conteo físico realizado por administración.',
        $fixture['admin'],
        (string) Str::uuid(),
    );

    expect($retry->id)->toBe($first->id)
        ->and($fixture['product']->fresh()->stock)->toBe(6)
        ->and(InventoryAdjustment::count())->toBe(2)
        ->and(StockMovement::count())->toBe(3)
        ->and((int) StockMovement::sum('quantity'))->toBe(6)
        ->and(AuditLog::whereIn('event', ['inventory.adjusted', 'inventory.count_corrected'])->count())->toBe(2);
    $this->artisan('inventory:reconcile')->assertSuccessful()->expectsOutputToContain('0 discrepancies');
});

test('adjustment validation and authorization reject manipulated requests', function () {
    $fixture = inventoryFixture();
    $service = app(InventoryService::class);
    $call = fn (User $actor, string $mode = 'decrease', int $value = 1, string $reason = 'Motivo administrativo suficientemente detallado.') => $service->adjust(
        $fixture['product']->id,
        $mode,
        $value,
        $reason,
        $actor,
        (string) Str::uuid(),
    );

    expect(fn () => $call($fixture['accountant']))->toThrow(HttpException::class)
        ->and(fn () => $call($fixture['vendor']))->toThrow(HttpException::class);
    $fixture['admin']->update(['is_active' => false]);
    expect(fn () => $call($fixture['admin']))->toThrow(HttpException::class);
    $fixture['admin']->update(['is_active' => true]);
    expect(fn () => $call($fixture['admin'], 'decrease', 6))->toThrow(ValidationException::class)
        ->and(fn () => $call($fixture['admin'], 'decrease', -1))->toThrow(ValidationException::class)
        ->and(fn () => $call($fixture['admin'], 'increase', 1000001))->toThrow(ValidationException::class)
        ->and(fn () => $service->adjust($fixture['product']->id, 'increase', 1.5, 'Motivo administrativo suficientemente detallado.', $fixture['admin'], (string) Str::uuid()))->toThrow(ValidationException::class)
        ->and(fn () => $service->adjust(999999, 'increase', 1, 'Motivo administrativo suficientemente detallado.', $fixture['admin'], (string) Str::uuid()))->toThrow(ModelNotFoundException::class)
        ->and(fn () => $call($fixture['admin'], 'increase', 1, 'ok'))->toThrow(ValidationException::class)
        ->and($fixture['product']->fresh()->stock)->toBe(5)
        ->and(InventoryAdjustment::count())->toBe(0);

    $fixture['product']->update(['is_active' => false]);
    expect(fn () => $call($fixture['admin'], 'increase', 1))->toThrow(ValidationException::class)
        ->and($fixture['product']->fresh()->stock)->toBe(5)
        ->and(InventoryAdjustment::count())->toBe(0);
});

test('stock and ledger history resist mass assignment edits and deletion', function () {
    $fixture = inventoryFixture();
    $product = $fixture['product'];
    $movement = $product->stockMovements()->firstOrFail();

    $product->fill(['stock' => 500]);
    expect($product->stock)->toBe(5)
        ->and(fn () => $product->forceFill(['stock' => 500])->save())->toThrow(LogicException::class)
        ->and(fn () => $movement->update(['quantity' => 500]))->toThrow(LogicException::class)
        ->and(fn () => $movement->delete())->toThrow(LogicException::class)
        ->and(fn () => $product->delete())->toThrow(LogicException::class)
        ->and($product->fresh()->stock)->toBe(5);
});

test('reconciliation reports drift without repairing it', function () {
    $fixture = inventoryFixture();
    $this->artisan('inventory:reconcile')->assertSuccessful();

    DB::table('products')->where('id', $fixture['product']->id)->update(['stock' => 8]);

    $this->artisan('inventory:reconcile')
        ->assertFailed()
        ->expectsOutputToContain('difference: +3');
    expect($fixture['product']->fresh()->stock)->toBe(8)
        ->and((int) StockMovement::sum('quantity'))->toBe(5);
});

test('inventory livewire allows viewing but restricts mutations to administrators', function () {
    $fixture = inventoryFixture();

    Livewire::actingAs($fixture['admin'])
        ->test(InventoryPanel::class)
        ->assertSee('Producto inventario')
        ->call('requestAdjustment', $fixture['product']->id)
        ->set('adjustmentMode', 'increase')
        ->set('adjustmentValue', 2)
        ->set('adjustmentReason', 'Ajuste confirmado desde el flujo funcional.')
        ->call('processAdjustment')
        ->assertHasNoErrors();

    expect($fixture['product']->fresh()->stock)->toBe(7);

    Livewire::actingAs($fixture['accountant'])
        ->test(InventoryPanel::class)
        ->assertSee('Producto inventario')
        ->assertDontSeeHtml('wire:click="requestAdjustment')
        ->call('requestAdjustment', $fixture['product']->id)
        ->assertForbidden();
});

test('search filters and archived products remain available for historical kardex', function () {
    $fixture = inventoryFixture();
    $from = now()->subDay()->toDateString();
    $to = now()->toDateString();
    Livewire::actingAs($fixture['admin'])
        ->test(InventoryPanel::class)
        ->set('searching', 'INV-001')
        ->assertSee('Producto inventario')
        ->call('archive', $fixture['product']->id)
        ->set('statusFilter', 'inactive')
        ->assertSee('Producto inventario')
        ->call('showKardex', $fixture['product']->id)
        ->assertSee('INITIAL BALANCE')
        ->set('kardexFrom', $from)
        ->set('kardexTo', $to)
        ->call('exportKardex')
        ->assertFileDownloaded("kardex-INV-001-{$from}-{$to}.xlsx");

    expect($fixture['product']->fresh()->is_active)->toBeFalse()
        ->and(AuditLog::where('event', 'inventory.product_archived')->count())->toBe(1);
});

test('product crud creates zero stock validates unique codes and supports reactivation', function () {
    $fixture = inventoryFixture();
    $component = Livewire::actingAs($fixture['admin'])
        ->test(InventoryPanel::class)
        ->call('create')
        ->set('product.code', 'INV-NEW')
        ->set('product.name', 'Producto nuevo')
        ->set('product.description', 'Descripción válida')
        ->set('product.brand', 'Marca')
        ->set('product.model', 'Modelo')
        ->set('product.supplier_id', $fixture['supplier']->id)
        ->set('product.category_id', $fixture['category']->id)
        ->set('product.min_stock', 0)
        ->set('product.price', '9.99')
        ->call('save')
        ->assertHasNoErrors();

    $created = Product::where('code', 'INV-NEW')->firstOrFail();
    expect($created->stock)->toBe(0);

    $component->call('archive', $created->id);
    expect($created->fresh()->is_active)->toBeFalse();
    $component->call('reactivate', $created->id);
    expect($created->fresh()->is_active)->toBeTrue();

    $component->call('create')
        ->set('product.code', 'INV-NEW')
        ->set('product.name', 'Duplicado')
        ->set('product.description', 'Descripción válida')
        ->set('product.brand', 'Marca')
        ->set('product.model', 'Modelo')
        ->set('product.supplier_id', $fixture['supplier']->id)
        ->set('product.category_id', $fixture['category']->id)
        ->set('product.min_stock', 0)
        ->set('product.price', '9.99')
        ->call('save')
        ->assertHasErrors(['product.code']);
});

test('inventory listing remains server paginated with a large catalog', function () {
    $fixture = inventoryFixture();
    $now = now();
    foreach (array_chunk(range(1, 1500), 500) as $chunk) {
        DB::table('products')->insert(array_map(fn (int $index): array => [
            'code' => 'BULK-'.str_pad((string) $index, 5, '0', STR_PAD_LEFT),
            'name' => 'Producto masivo '.$index,
            'description' => null,
            'brand' => 'Marca',
            'model' => 'M',
            'supplier_id' => $fixture['supplier']->id,
            'category_id' => $fixture['category']->id,
            'stock' => 0,
            'min_stock' => 0,
            'price' => '1.00',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], $chunk));
    }

    Livewire::actingAs($fixture['accountant'])
        ->test(InventoryPanel::class)
        ->assertViewHas('products', fn ($products): bool => $products->count() === 25 && $products->total() === 1501)
        ->set('searching', 'BULK-01500')
        ->assertSee('Producto masivo 1500')
        ->assertDontSee('Producto masivo 1499');
});
