<?php

namespace Tests\Feature\Inventory;

use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\ReturnService;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;
use Throwable;

function inventoryRaceFixture(string $suffix, int $stock): array
{
    $vendorRole = Role::firstOrCreate(['name' => 'Vendedor']);
    $adminRole = Role::firstOrCreate(['name' => 'Administrador']);
    $vendor = User::factory()->create(['role_id' => $vendorRole->id, 'is_active' => true]);
    $admin = User::factory()->create(['role_id' => $adminRole->id, 'is_active' => true]);
    $register = CashRegister::create(['name' => 'Caja '.$suffix, 'code' => 'RACE-'.$suffix, 'is_active' => true]);
    CashSession::create([
        'cash_register_id' => $register->id,
        'user_id' => $vendor->id,
        'opening_amount' => '1000.00',
        'status' => 'open',
        'opened_at' => now(),
    ]);
    $customerType = CustomerType::firstOrCreate(['name' => 'General']);
    $customer = Customer::create([
        'dni_ruc' => 'RACE-'.$suffix,
        'name' => 'Cliente '.$suffix,
        'address' => 'Managua',
        'city' => 'Managua',
        'customer_type_id' => $customerType->id,
        'is_active' => true,
    ]);
    $supplier = Supplier::create(['name' => 'Proveedor '.$suffix, 'is_active' => true]);
    $category = Category::create(['name' => 'Categoría '.$suffix]);
    $product = Product::create([
        'code' => 'RACE-'.$suffix,
        'name' => 'Producto '.$suffix,
        'brand' => 'Marca',
        'model' => 'M1',
        'supplier_id' => $supplier->id,
        'category_id' => $category->id,
        'min_stock' => 0,
        'price' => '10.00',
        'is_active' => true,
    ]);
    app(InventoryService::class)->initialize($product, $stock, $admin);
    $cash = PaymentMethod::firstOrCreate(
        ['code' => 'CASH'],
        ['name' => 'Efectivo', 'affects_cash_drawer' => true, 'is_active' => true],
    );
    $transfer = PaymentMethod::firstOrCreate(
        ['code' => 'TRANSFER'],
        ['name' => 'Transferencia', 'requires_reference' => true, 'affects_cash_drawer' => false, 'is_active' => true],
    );

    return compact('vendor', 'admin', 'customer', 'product', 'cash', 'transfer');
}

function runInventoryRace(array $operations): array
{
    $token = str_replace('-', '', (string) Str::uuid());
    $prefix = sys_get_temp_dir().DIRECTORY_SEPARATOR.'leo-inventory-'.$token;
    $startFile = $prefix.'-start';
    $children = [];

    DB::disconnect();
    foreach ($operations as $index => $operation) {
        $readyFile = $prefix."-ready-{$index}";
        $resultFile = $prefix."-result-{$index}";
        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new RuntimeException('No fue posible iniciar la carrera de inventario.');
        }
        if ($pid === 0) {
            DB::purge();
            file_put_contents($readyFile, 'ready');
            $deadline = microtime(true) + 10;
            while (! file_exists($startFile) && microtime(true) < $deadline) {
                usleep(1000);
            }

            try {
                $resultId = match ($operation['type']) {
                    'sale' => app(SaleService::class)->create(
                        [['id' => $operation['product_id'], 'quantity' => $operation['quantity']]],
                        $operation['customer_id'],
                        $operation['payment_method_id'],
                        100,
                        User::findOrFail($operation['actor_id']),
                    )->id,
                    'adjust' => app(InventoryService::class)->adjust(
                        $operation['product_id'],
                        'decrease',
                        $operation['quantity'],
                        'Salida concurrente autorizada para prueba.',
                        User::findOrFail($operation['actor_id']),
                        (string) Str::uuid(),
                    )->id,
                    'return' => app(ReturnService::class)->process(
                        $operation['sale_id'],
                        [[
                            'sale_detail_id' => $operation['sale_detail_id'],
                            'quantity' => $operation['quantity'],
                            'restock' => true,
                            'condition' => 'sellable',
                        ]],
                        $operation['payment_method_id'],
                        'Devolución concurrente autorizada para prueba.',
                        User::findOrFail($operation['actor_id']),
                        (string) Str::uuid(),
                        'RACE-RETURN',
                    )->id,
                };
                $result = ['success' => true, 'id' => $resultId];
            } catch (Throwable $exception) {
                $result = ['success' => false, 'error' => $exception::class];
            }
            file_put_contents($resultFile, json_encode($result, JSON_THROW_ON_ERROR));
            exit(0);
        }
        $children[] = compact('pid', 'readyFile', 'resultFile');
    }

    $deadline = microtime(true) + 10;
    while (collect($children)->contains(fn (array $child): bool => ! file_exists($child['readyFile'])) && microtime(true) < $deadline) {
        usleep(1000);
    }
    touch($startFile);
    foreach ($children as $child) {
        pcntl_waitpid($child['pid'], $status);
    }
    $results = collect($children)->map(fn (array $child): array => json_decode(
        (string) file_get_contents($child['resultFile']),
        true,
        flags: JSON_THROW_ON_ERROR,
    ))->all();
    foreach ([$startFile, ...collect($children)->pluck('readyFile'), ...collect($children)->pluck('resultFile')] as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }
    DB::purge();

    return $results;
}

class InventoryConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    public function test_inventory_operations_serialize_without_negative_stock_or_ledger_drift(): void
    {
        $lastUnit = inventoryRaceFixture('LAST', 1);
        $saleOperation = fn (array $fixture, int $quantity): array => [
            'type' => 'sale',
            'product_id' => $fixture['product']->id,
            'quantity' => $quantity,
            'customer_id' => $fixture['customer']->id,
            'payment_method_id' => $fixture['cash']->id,
            'actor_id' => $fixture['vendor']->id,
        ];
        $lastResults = runInventoryRace([$saleOperation($lastUnit, 1), $saleOperation($lastUnit, 1)]);
        expect(collect($lastResults)->where('success', true))->toHaveCount(1)
            ->and(collect($lastResults)->where('success', false))->toHaveCount(1)
            ->and($lastUnit['product']->fresh()->stock)->toBe(0)
            ->and(StockMovement::where('product_id', $lastUnit['product']->id)->where('type', 'sale')->count())->toBe(1);

        $adjustVsSale = inventoryRaceFixture('ADJUST', 5);
        $mixedResults = runInventoryRace([
            $saleOperation($adjustVsSale, 5),
            [
                'type' => 'adjust',
                'product_id' => $adjustVsSale['product']->id,
                'quantity' => 1,
                'actor_id' => $adjustVsSale['admin']->id,
            ],
        ]);
        $mixedStock = $adjustVsSale['product']->fresh()->stock;
        expect(collect($mixedResults)->where('success', true))->toHaveCount(1)
            ->and(collect($mixedResults)->where('success', false))->toHaveCount(1)
            ->and($mixedStock)->toBeGreaterThanOrEqual(0)
            ->and((int) StockMovement::where('product_id', $adjustVsSale['product']->id)->sum('quantity'))->toBe($mixedStock);

        $returnVsSale = inventoryRaceFixture('RETURN', 1);
        $historicalSale = app(SaleService::class)->create(
            [['id' => $returnVsSale['product']->id, 'quantity' => 1]],
            $returnVsSale['customer']->id,
            $returnVsSale['cash']->id,
            100,
            $returnVsSale['vendor'],
        );
        $returnResults = runInventoryRace([
            $saleOperation($returnVsSale, 1),
            [
                'type' => 'return',
                'sale_id' => $historicalSale->id,
                'sale_detail_id' => $historicalSale->saleDetails()->first()->id,
                'quantity' => 1,
                'payment_method_id' => $returnVsSale['transfer']->id,
                'actor_id' => $returnVsSale['admin']->id,
            ],
        ]);
        $returnStock = $returnVsSale['product']->fresh()->stock;
        expect(collect($returnResults)->where('success', true)->count())->toBeGreaterThanOrEqual(1)
            ->and(StockMovement::where('product_id', $returnVsSale['product']->id)->where('type', 'customer_return')->count())->toBe(1)
            ->and($returnStock)->toBeGreaterThanOrEqual(0)
            ->and((int) StockMovement::where('product_id', $returnVsSale['product']->id)->sum('quantity'))->toBe($returnStock);
    }
}
