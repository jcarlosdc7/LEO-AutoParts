<?php

namespace Tests\Feature\Financial;

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
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
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

function concurrentReturnFixture(): array
{
    $vendorRole = Role::firstOrCreate(['name' => 'Vendedor']);
    $adminRole = Role::firstOrCreate(['name' => 'Administrador']);
    $vendor = User::factory()->create(['role_id' => $vendorRole->id, 'is_active' => true]);
    $admin = User::factory()->create(['role_id' => $adminRole->id, 'is_active' => true]);
    $register = CashRegister::create(['name' => 'Caja concurrencia', 'code' => 'CON-01', 'is_active' => true]);
    CashSession::create([
        'cash_register_id' => $register->id,
        'user_id' => $vendor->id,
        'opening_amount' => '100.00',
        'status' => 'open',
        'opened_at' => now(),
    ]);
    $customerType = CustomerType::create(['name' => 'General']);
    $customer = Customer::create([
        'dni_ruc' => 'CON-001',
        'name' => 'Cliente concurrente',
        'address' => 'Managua',
        'city' => 'Managua',
        'customer_type_id' => $customerType->id,
        'is_active' => true,
    ]);
    $supplier = Supplier::create(['name' => 'Proveedor', 'is_active' => true]);
    $category = Category::create(['name' => 'Categoría']);
    $product = Product::create([
        'code' => 'CON-P1',
        'name' => 'Pieza concurrente',
        'brand' => 'Marca',
        'model' => 'M1',
        'supplier_id' => $supplier->id,
        'category_id' => $category->id,
        'stock' => 10,
        'min_stock' => 1,
        'price' => '10.10',
        'is_active' => true,
    ]);
    app(InventoryService::class)->initialize($product, 10);
    $cash = PaymentMethod::create([
        'code' => 'CASH',
        'name' => 'Efectivo',
        'affects_cash_drawer' => true,
        'is_active' => true,
    ]);
    $transfer = PaymentMethod::create([
        'code' => 'TRANSFER',
        'name' => 'Transferencia',
        'requires_reference' => true,
        'affects_cash_drawer' => false,
        'is_active' => true,
    ]);
    $sale = app(SaleService::class)->create(
        [['id' => $product->id, 'quantity' => 5]],
        $customer->id,
        $cash->id,
        60,
        $vendor,
    );

    return compact('vendor', 'admin', 'product', 'cash', 'transfer', 'sale');
}

function completedConcurrentReturn(array $fixture, int $quantity): SaleReturn
{
    return app(ReturnService::class)->process(
        $fixture['sale'],
        [[
            'sale_detail_id' => $fixture['sale']->saleDetails()->first()->id,
            'quantity' => $quantity,
            'restock' => false,
            'condition' => 'defective',
        ]],
        $fixture['transfer']->id,
        'Devolución previa autorizada para concurrencia.',
        $fixture['admin'],
        (string) Str::uuid(),
        'PREVIOUS',
    );
}

function concurrentAttempt(array $fixture, int $quantity, string $operationId, ?int $saleId = null): array
{
    $sale = $saleId ? Sale::findOrFail($saleId) : $fixture['sale'];

    return [
        'sale_id' => $sale->id,
        'sale_detail_id' => $sale->saleDetails()->first()->id,
        'quantity' => $quantity,
        'payment_method_id' => $fixture['transfer']->id,
        'actor_id' => $fixture['admin']->id,
        'operation_id' => $operationId,
    ];
}

function runConcurrentReturns(array $attempts): array
{
    $token = str_replace('-', '', (string) Str::uuid());
    $prefix = sys_get_temp_dir().DIRECTORY_SEPARATOR.'leo-return-'.$token;
    $startFile = $prefix.'-start';
    $children = [];

    DB::disconnect();
    foreach ($attempts as $index => $attempt) {
        $readyFile = $prefix."-ready-{$index}";
        $resultFile = $prefix."-result-{$index}";
        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new RuntimeException('No fue posible iniciar el proceso concurrente.');
        }
        if ($pid === 0) {
            DB::purge();
            file_put_contents($readyFile, 'ready');
            $deadline = microtime(true) + 10;
            while (! file_exists($startFile) && microtime(true) < $deadline) {
                usleep(1000);
            }

            try {
                $saleReturn = app(ReturnService::class)->process(
                    $attempt['sale_id'],
                    [[
                        'sale_detail_id' => $attempt['sale_detail_id'],
                        'quantity' => $attempt['quantity'],
                        'restock' => false,
                        'condition' => 'defective',
                    ]],
                    $attempt['payment_method_id'],
                    'Devolución concurrente autorizada para prueba.',
                    User::findOrFail($attempt['actor_id']),
                    $attempt['operation_id'],
                    'CONCURRENT-'.$index,
                );
                $result = ['success' => true, 'return_id' => $saleReturn->id];
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

class ReturnConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    public function test_two_concurrent_requests_can_consume_the_last_unit_only_once(): void
    {
        $fixture = concurrentReturnFixture();
        completedConcurrentReturn($fixture, 4);
        $results = runConcurrentReturns([
            concurrentAttempt($fixture, 1, (string) Str::uuid()),
            concurrentAttempt($fixture, 1, (string) Str::uuid()),
        ]);

        expect(collect($results)->where('success', true))->toHaveCount(1)
            ->and(collect($results)->where('success', false))->toHaveCount(1)
            ->and((int) SaleReturnItem::sum('quantity'))->toBe(5)
            ->and(Refund::count())->toBe(2);
    }

    public function test_two_concurrent_partial_returns_cannot_exceed_the_remaining_quantity(): void
    {
        $fixture = concurrentReturnFixture();
        completedConcurrentReturn($fixture, 3);
        $results = runConcurrentReturns([
            concurrentAttempt($fixture, 2, (string) Str::uuid()),
            concurrentAttempt($fixture, 2, (string) Str::uuid()),
        ]);

        expect(collect($results)->where('success', true))->toHaveCount(1)
            ->and(collect($results)->where('success', false))->toHaveCount(1)
            ->and((int) SaleReturnItem::sum('quantity'))->toBe(5);
    }

    public function test_concurrent_retries_with_one_operation_id_create_one_refund(): void
    {
        $fixture = concurrentReturnFixture();
        $operationId = (string) Str::uuid();
        $results = runConcurrentReturns([
            concurrentAttempt($fixture, 1, $operationId),
            concurrentAttempt($fixture, 1, $operationId),
        ]);

        expect(collect($results)->where('success', true))->toHaveCount(2)
            ->and(collect($results)->pluck('return_id')->unique())->toHaveCount(1)
            ->and(SaleReturn::count())->toBe(1)
            ->and(Refund::count())->toBe(1)
            ->and(CreditNote::count())->toBe(1);
    }

    public function test_concurrent_returns_issue_unique_return_and_credit_note_numbers(): void
    {
        $fixture = concurrentReturnFixture();
        $secondSale = app(SaleService::class)->create(
            [['id' => $fixture['product']->id, 'quantity' => 1]],
            $fixture['sale']->customer_id,
            $fixture['cash']->id,
            20,
            $fixture['vendor'],
        );
        $results = runConcurrentReturns([
            concurrentAttempt($fixture, 1, (string) Str::uuid()),
            concurrentAttempt($fixture, 1, (string) Str::uuid(), $secondSale->id),
        ]);

        expect(collect($results)->where('success', true))->toHaveCount(2)
            ->and(SaleReturn::pluck('return_number')->unique())->toHaveCount(2)
            ->and(CreditNote::pluck('number')->unique())->toHaveCount(2);
    }
}
