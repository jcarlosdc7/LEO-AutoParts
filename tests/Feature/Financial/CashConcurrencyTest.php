<?php

namespace Tests\Feature\Financial;

use App\Models\CashCount;
use App\Models\CashDenomination;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\Role;
use App\Models\User;
use App\Services\CashService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;
use Throwable;

function runConcurrentCashActions(array $attempts): array
{
    $prefix = sys_get_temp_dir().DIRECTORY_SEPARATOR.'leo-cash-'.str_replace('-', '', (string) Str::uuid());
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
                $service = app(CashService::class);
                $actor = User::findOrFail($attempt['actor_id']);
                $result = match ($attempt['action']) {
                    'open' => $service->open($attempt['register_code'], $attempt['counts'], null, $actor, $attempt['operation_id']),
                    'close' => $service->close($attempt['session_id'], $attempt['counts'], $attempt['reason'] ?? null, $actor, $attempt['operation_id']),
                    'movement' => $service->recordMovement(
                        $attempt['session_id'], 'income', '10.00', 'Ingreso concurrente documentado.',
                        null, $actor, $attempt['operation_id'],
                    ),
                };
                $payload = ['success' => true, 'id' => $result->id];
            } catch (Throwable $exception) {
                $payload = ['success' => false, 'error' => $exception::class];
            }

            file_put_contents($resultFile, json_encode($payload, JSON_THROW_ON_ERROR));
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

class CashConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    private User $admin;

    private CashRegister $register;

    private array $hundred;

    protected function setUp(): void
    {
        parent::setUp();
        $role = Role::firstOrCreate(['name' => 'Administrador']);
        $this->admin = User::factory()->create(['role_id' => $role->id, 'is_active' => true]);
        $this->register = CashRegister::create([
            'name' => 'Caja concurrente', 'code' => 'CASH-CON', 'currency_code' => 'NIO', 'is_active' => true,
        ]);
        $denomination = CashDenomination::query()->where('currency_code', 'NIO')->where('value', '100.00')->where('type', 'BANKNOTE')->firstOrFail();
        $this->hundred = [$denomination->id => 1];
    }

    public function test_two_users_opening_one_register_produce_exactly_one_session(): void
    {
        $second = User::factory()->create(['role_id' => $this->admin->role_id, 'is_active' => true]);
        $results = runConcurrentCashActions([
            ['action' => 'open', 'register_code' => $this->register->code, 'counts' => $this->hundred, 'actor_id' => $this->admin->id, 'operation_id' => (string) Str::uuid()],
            ['action' => 'open', 'register_code' => $this->register->code, 'counts' => $this->hundred, 'actor_id' => $second->id, 'operation_id' => (string) Str::uuid()],
        ]);

        expect(collect($results)->where('success', true))->toHaveCount(1)
            ->and(collect($results)->where('success', false))->toHaveCount(1)
            ->and(CashSession::where('status', 'open')->count())->toBe(1)
            ->and(CashCount::where('type', 'OPENING')->count())->toBe(1);
    }

    public function test_two_concurrent_closures_create_one_closing_count(): void
    {
        $session = app(CashService::class)->open($this->register->code, $this->hundred, null, $this->admin, (string) Str::uuid());
        $results = runConcurrentCashActions([
            ['action' => 'close', 'session_id' => $session->id, 'counts' => $this->hundred, 'actor_id' => $this->admin->id, 'operation_id' => (string) Str::uuid()],
            ['action' => 'close', 'session_id' => $session->id, 'counts' => $this->hundred, 'actor_id' => $this->admin->id, 'operation_id' => (string) Str::uuid()],
        ]);

        expect(collect($results)->where('success', true))->toHaveCount(1)
            ->and(collect($results)->where('success', false))->toHaveCount(1)
            ->and(CashCount::where('type', 'CLOSING')->count())->toBe(1)
            ->and($session->fresh()->status)->toBe('closed');
    }

    public function test_movement_and_close_are_serialized_without_post_close_mutation(): void
    {
        $session = app(CashService::class)->open($this->register->code, $this->hundred, null, $this->admin, (string) Str::uuid());
        $results = runConcurrentCashActions([
            ['action' => 'movement', 'session_id' => $session->id, 'actor_id' => $this->admin->id, 'operation_id' => (string) Str::uuid()],
            ['action' => 'close', 'session_id' => $session->id, 'counts' => $this->hundred, 'reason' => 'Conciliación concurrente verificada.', 'actor_id' => $this->admin->id, 'operation_id' => (string) Str::uuid()],
        ]);

        $session->refresh();
        expect($session->status)->toBe('closed')
            ->and(CashCount::where('type', 'CLOSING')->count())->toBe(1)
            ->and(CashMovement::count())->toBeIn([0, 1])
            ->and($session->expected_amount)->toBe(CashMovement::count() === 1 ? '110.00' : '100.00')
            ->and(collect($results)->where('success', true)->count())->toBeGreaterThanOrEqual(1);
    }
}
