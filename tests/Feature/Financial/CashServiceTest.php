<?php

use App\Livewire\CashPanel;
use App\Models\AuditLog;
use App\Models\CashCount;
use App\Models\CashDenomination;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\Role;
use App\Models\User;
use App\Services\CashService;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

function cashActor(string $roleName = 'Administrador', bool $active = true): User
{
    $role = Role::firstOrCreate(['name' => $roleName]);

    return User::factory()->create(['role_id' => $role->id, 'is_active' => $active]);
}

function cashRegister(string $code = 'TEST-CASH'): CashRegister
{
    return CashRegister::create([
        'name' => 'Caja '.$code,
        'code' => $code,
        'currency_code' => 'NIO',
        'is_active' => true,
    ]);
}

function denominationCounts(array $values): array
{
    $counts = [];
    foreach ($values as $key => $quantity) {
        [$value, $type] = array_pad(explode('|', $key, 2), 2, 'BANKNOTE');
        $denomination = CashDenomination::query()
            ->where('currency_code', 'NIO')
            ->where('value', $value)
            ->where('type', $type)
            ->firstOrFail();
        $counts[$denomination->id] = $quantity;
    }

    return $counts;
}

test('authorized user opens a register atomically with an exact immutable denomination count', function () {
    $actor = cashActor();
    $register = cashRegister();
    $operationId = (string) Str::uuid();

    $session = app(CashService::class)->open(
        $register->code,
        denominationCounts(['100.00' => 1, '20.00' => 2, '0.50|COIN' => 3]),
        'Fondo inicial contado por el responsable.',
        $actor,
        $operationId,
    );

    expect($session->status)->toBe('open')
        ->and($session->opening_amount)->toBe('141.50')
        ->and($session->opening_operation_id)->toBe($operationId)
        ->and($session->openingCount->total)->toBe('141.50')
        ->and($session->openingCount->lines)->toHaveCount(12)
        ->and($session->openingCount->lines->sum('quantity'))->toBe(6)
        ->and(AuditLog::where('event', 'cash.opened')->count())->toBe(1);

    expect(fn () => $session->openingCount->lines->first()->forceFill(['quantity' => 99])->save())
        ->toThrow(LogicException::class);
});

test('opening is idempotent and one user cannot own two open sessions', function () {
    $actor = cashActor();
    $register = cashRegister('IDEM-1');
    $operationId = (string) Str::uuid();
    $service = app(CashService::class);

    $first = $service->open($register->code, denominationCounts(['100.00' => 1]), null, $actor, $operationId);
    $retry = $service->open($register->code, denominationCounts(['100.00' => 9]), null, $actor, $operationId);

    expect($retry->id)->toBe($first->id)
        ->and(CashSession::count())->toBe(1)
        ->and(CashCount::count())->toBe(1);

    $otherRegister = cashRegister('IDEM-2');
    expect(fn () => $service->open($otherRegister->code, denominationCounts(['20.00' => 1]), null, $actor, (string) Str::uuid()))
        ->toThrow(ValidationException::class);
});

test('unauthorized and inactive users cannot open a cash register', function () {
    $register = cashRegister('AUTH-1');
    $counts = denominationCounts(['20.00' => 1]);

    expect(fn () => app(CashService::class)->open($register->code, $counts, null, cashActor('Contador'), (string) Str::uuid()))
        ->toThrow(HttpException::class)
        ->and(fn () => app(CashService::class)->open($register->code, $counts, null, cashActor('Administrador', false), (string) Str::uuid()))
        ->toThrow(HttpException::class)
        ->and(CashSession::count())->toBe(0);
});

test('manual movements are idempotent serialized and cannot make cash negative', function () {
    $actor = cashActor();
    $register = cashRegister('MOVE-1');
    $service = app(CashService::class);
    $session = $service->open($register->code, denominationCounts(['100.00' => 1]), null, $actor, (string) Str::uuid());
    $expenseOperation = (string) Str::uuid();

    $expense = $service->recordMovement($session->id, 'expense', '40', 'Compra urgente de materiales.', 'REC-01', $actor, $expenseOperation);
    $retry = $service->recordMovement($session->id, 'expense', '40.00', 'Compra urgente de materiales.', 'REC-01', $actor, $expenseOperation);

    expect($retry->id)->toBe($expense->id)
        ->and(CashMovement::count())->toBe(1)
        ->and($service->expectedCash($session))->toBe('60.00');

    expect(fn () => $service->recordMovement($session->id, 'withdrawal', '60.01', 'Retiro superior al saldo disponible.', null, $actor, (string) Str::uuid()))
        ->toThrow(ValidationException::class)
        ->and(CashMovement::count())->toBe(1);

    $service->recordMovement($session->id, 'deposit', '10.25', 'Depósito adicional documentado.', 'DEP-1', $actor, (string) Str::uuid());
    expect($service->expectedCash($session))->toBe('70.25')
        ->and(AuditLog::where('event', 'cash.movement.created')->count())->toBe(2);
});

test('session ownership prevents IDOR and historical mutations', function () {
    $owner = cashActor('Vendedor');
    $intruder = cashActor('Vendedor');
    $register = cashRegister('IDOR-1');
    $session = app(CashService::class)->open($register->code, denominationCounts(['50.00' => 1]), null, $owner, (string) Str::uuid());

    expect(fn () => app(CashService::class)->recordMovement($session->id, 'income', '10.00', 'Intento sobre sesión ajena.', null, $intruder, (string) Str::uuid()))
        ->toThrow(HttpException::class)
        ->and(fn () => app(CashService::class)->close($session->id, denominationCounts(['50.00' => 1]), null, $intruder, (string) Str::uuid()))
        ->toThrow(HttpException::class)
        ->and(CashMovement::count())->toBe(0);
});

test('inactive users cannot move or close an existing cash session', function () {
    $actor = cashActor('Vendedor');
    $register = cashRegister('INACTIVE-OPS');
    $service = app(CashService::class);
    $session = $service->open($register->code, denominationCounts(['50.00' => 1]), null, $actor, (string) Str::uuid());
    $actor->forceFill(['is_active' => false])->save();

    expect(fn () => $service->recordMovement($session->id, 'income', '10.00', 'Intento con usuario inactivo.', null, $actor, (string) Str::uuid()))
        ->toThrow(HttpException::class)
        ->and(fn () => $service->close($session->id, denominationCounts(['50.00' => 1]), null, $actor, (string) Str::uuid()))
        ->toThrow(HttpException::class)
        ->and($session->fresh()->status)->toBe('open')
        ->and(CashMovement::count())->toBe(0);
});

test('cash financial models reject mass assignment of protected fields', function () {
    $actor = cashActor();
    $register = cashRegister('MASS-ASSIGNMENT');

    expect(fn () => CashSession::create([
        'cash_register_id' => $register->id,
        'user_id' => $actor->id,
        'opening_amount' => '999999.99',
        'status' => 'closed',
        'closed_by' => $actor->id,
    ]))->toThrow(MassAssignmentException::class)
        ->and(fn () => CashMovement::create([
            'cash_session_id' => 999999,
            'user_id' => $actor->id,
            'type' => 'income',
            'amount' => '999999.99',
        ]))->toThrow(MassAssignmentException::class)
        ->and(fn () => CashCount::create([
            'cash_session_id' => 999999,
            'operation_id' => (string) Str::uuid(),
            'type' => 'CLOSING',
            'total' => '999999.99',
        ]))->toThrow(MassAssignmentException::class);
});

test('blind closing records exact reconciliation and resists duplicate closure', function () {
    $actor = cashActor();
    $register = cashRegister('CLOSE-1');
    $service = app(CashService::class);
    $session = $service->open($register->code, denominationCounts(['100.00' => 1]), null, $actor, (string) Str::uuid());
    $operationId = (string) Str::uuid();
    $counts = denominationCounts(['50.00' => 1, '20.00' => 2, '10.00' => 1]);

    $preview = $service->previewClose($session->id, $counts, $actor);
    $closed = $service->close($session->id, $counts, null, $actor, $operationId);
    $retry = $service->close($session->id, $counts, null, $actor, $operationId);

    expect($preview)->toMatchArray(['expected' => '100.00', 'counted' => '100.00', 'difference' => '0.00', 'status' => 'CUADRA'])
        ->and($closed->status)->toBe('closed')
        ->and($closed->expected_amount)->toBe('100.00')
        ->and($closed->closing_amount)->toBe('100.00')
        ->and($closed->difference)->toBe('0.00')
        ->and($retry->id)->toBe($closed->id)
        ->and(CashCount::where('type', 'CLOSING')->count())->toBe(1)
        ->and(AuditLog::where('event', 'cash.closed')->count())->toBe(1);

    expect(fn () => $closed->forceFill(['difference' => '1.00'])->save())
        ->toThrow(LogicException::class)
        ->and(fn () => $closed->delete())
        ->toThrow(LogicException::class);
});

test('shortage and surplus are server calculated and require a meaningful reason', function (string $countedValue, string $expectedDifference, string $status) {
    $actor = cashActor();
    $register = cashRegister('DIFF-'.$status);
    $service = app(CashService::class);
    $session = $service->open($register->code, denominationCounts(['100.00' => 1]), null, $actor, (string) Str::uuid());
    $counts = denominationCounts([$countedValue => 1]);

    $preview = $service->previewClose($session->id, $counts, $actor);
    expect($preview['difference'])->toBe($expectedDifference)->and($preview['status'])->toBe($status);

    expect(fn () => $service->close($session->id, $counts, 'ok', $actor, (string) Str::uuid()))
        ->toThrow(ValidationException::class)
        ->and($session->fresh()->status)->toBe('open')
        ->and(CashCount::where('type', 'CLOSING')->count())->toBe(0);

    $closed = $service->close($session->id, $counts, 'Diferencia verificada durante el conteo físico.', $actor, (string) Str::uuid());
    expect($closed->difference)->toBe($expectedDifference)
        ->and(AuditLog::where('event', 'cash.difference.recorded')->count())->toBe(1);
})->with([
    'shortage' => ['50.00', '-50.00', 'FALTANTE'],
    'surplus' => ['200.00', '100.00', 'SOBRANTE'],
]);

test('forged denomination and forged total are rejected and recalculated', function () {
    $actor = cashActor();
    $register = cashRegister('FORGE-1');
    $service = app(CashService::class);

    expect(fn () => $service->open($register->code, [999999 => 1], null, $actor, (string) Str::uuid()))
        ->toThrow(ValidationException::class)
        ->and(CashSession::count())->toBe(0);

    $session = $service->open($register->code, denominationCounts(['20.00' => 3]), null, $actor, (string) Str::uuid());
    expect($session->opening_amount)->toBe('60.00');
});

test('closed cash records and count lines cannot be deleted', function () {
    $actor = cashActor();
    $register = cashRegister('IMM-1');
    $service = app(CashService::class);
    $session = $service->open($register->code, denominationCounts(['20.00' => 1]), null, $actor, (string) Str::uuid());
    $closed = $service->close($session->id, denominationCounts(['20.00' => 1]), null, $actor, (string) Str::uuid());

    expect(fn () => $closed->closingCount->delete())->toThrow(LogicException::class)
        ->and(fn () => $closed->closingCount->lines->first()->delete())->toThrow(LogicException::class)
        ->and(fn () => app(CashService::class)->recordMovement($closed->id, 'income', '1.00', 'Movimiento tardío rechazado.', null, $actor, (string) Str::uuid()))
        ->toThrow(ValidationException::class);
});

test('cash reconciliation rebuilds expected money without modifying history', function () {
    $actor = cashActor();
    $register = cashRegister('REC-1');
    $service = app(CashService::class);
    $session = $service->open($register->code, denominationCounts(['100.00' => 1]), null, $actor, (string) Str::uuid());
    $service->recordMovement($session->id, 'income', '20.00', 'Ingreso conciliable documentado.', null, $actor, (string) Str::uuid());
    $service->close($session->id, denominationCounts(['100.00' => 1, '20.00' => 1]), null, $actor, (string) Str::uuid());

    expect(Artisan::call('cash:reconcile'))->toBe(0)
        ->and(Artisan::output())->toContain('passed for 1 session');
});

test('cash livewire makes the next action obvious and opens from denomination inputs', function () {
    $actor = cashActor();
    $register = cashRegister('UI-1');
    $counts = denominationCounts(['100.00' => 1, '20.00' => 1]);

    $component = Livewire::actingAs($actor)->test(CashPanel::class)
        ->set('registerCode', $register->code)
        ->assertSee('CAJA CERRADA')
        ->assertSee('ABRIR CAJA')
        ->assertSeeHtml('role="dialog"')
        ->assertSeeHtml('aria-modal="true"');

    foreach ($counts as $denominationId => $quantity) {
        $component->set("openingCounts.{$denominationId}", $quantity);
    }

    $component->call('openSession')
        ->assertHasNoErrors()
        ->assertSee('CAJA ABIERTA')
        ->assertSee($register->name);

    expect(CashSession::where('cash_register_id', $register->id)->value('opening_amount'))->toBe('120.00');
});

test('sensitive withdrawals require administrator and approved withdrawals are audited', function () {
    $vendor = cashActor('Vendedor');
    $register = cashRegister('APPROVAL-1');
    $service = app(CashService::class);
    $session = $service->open($register->code, denominationCounts(['1000.00' => 20]), null, $vendor, (string) Str::uuid());

    expect(fn () => $service->recordMovement($session->id, 'withdrawal', '10000.00', 'Retiro sensible solicitado formalmente.', null, $vendor, (string) Str::uuid()))
        ->toThrow(ValidationException::class);

    $admin = cashActor();
    $movement = $service->recordMovement($session->id, 'withdrawal', '10000.00', 'Retiro sensible autorizado formalmente.', 'AUT-001', $admin, (string) Str::uuid());

    expect($movement->approved_by)->toBe($admin->id)
        ->and($movement->approved_at)->not->toBeNull()
        ->and(AuditLog::where('event', 'cash.withdrawal.approved')->count())->toBe(1);
});
