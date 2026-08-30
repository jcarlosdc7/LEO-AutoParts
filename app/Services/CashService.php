<?php

namespace App\Services;

use App\Models\CashCount;
use App\Models\CashCountLine;
use App\Models\CashDenomination;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\User;
use App\Support\Decimal;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class CashService
{
    public const INFLOW_TYPES = ['sale', 'income', 'deposit'];

    public const OUTFLOW_TYPES = ['expense', 'withdrawal', 'refund'];

    public const MANUAL_TYPES = ['income', 'deposit', 'expense', 'withdrawal'];

    public function open(string $registerCode, array $quantities, ?string $notes, User $actor, string $operationId): CashSession
    {
        $this->authorize($actor);
        $this->validateOperationId($operationId);
        $notes = $this->normalizeOptionalText($notes, 1000, 'openingNotes');

        return DB::transaction(function () use ($registerCode, $quantities, $notes, $actor, $operationId): CashSession {
            $register = CashRegister::query()
                ->where('code', $registerCode)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (! $register) {
                throw ValidationException::withMessages(['registerCode' => 'La caja seleccionada no está disponible.']);
            }

            $existing = CashSession::query()->where('opening_operation_id', $operationId)->first();
            if ($existing) {
                abort_unless($existing->cash_register_id === $register->id && $existing->user_id === $actor->id, 409, 'La clave de apertura pertenece a otra operación.');

                return $existing->load(['register', 'openingCount.lines.denomination']);
            }

            if (CashSession::query()->where('user_id', $actor->id)->where('status', 'open')->exists()) {
                throw ValidationException::withMessages(['registerCode' => 'Ya tienes una sesión de caja abierta.']);
            }

            if (CashSession::query()->where('cash_register_id', $register->id)->where('status', 'open')->exists()) {
                throw ValidationException::withMessages(['registerCode' => 'La caja ya fue abierta por otro usuario.']);
            }

            [$total, $lines] = $this->calculateCount($register->currency_code, $quantities);
            $session = CashSession::forceCreate([
                'cash_register_id' => $register->id,
                'user_id' => $actor->id,
                'opening_operation_id' => $operationId,
                'opening_amount' => $total,
                'opening_notes' => $notes,
                'status' => 'open',
                'opened_at' => now(),
            ]);
            $count = $this->persistCount($session, 'OPENING', $operationId, $total, $lines, $actor);

            AuditService::record('cash.opened', $session, [], [
                'cash_register_id' => $register->id,
                'cash_register_code' => $register->code,
                'cash_session_id' => $session->id,
                'cash_count_id' => $count->id,
                'amount' => $total,
                'currency_code' => $register->currency_code,
            ], $actor->id);

            return $session->load(['register', 'openingCount.lines.denomination']);
        }, 3);
    }

    public function recordMovement(
        int $sessionId,
        string $type,
        string $amount,
        string $reason,
        ?string $reference,
        User $actor,
        string $operationId,
    ): CashMovement {
        $this->authorize($actor);
        $this->validateOperationId($operationId);
        if (! in_array($type, self::MANUAL_TYPES, true)) {
            throw ValidationException::withMessages(['movementType' => 'El tipo de movimiento no está permitido.']);
        }
        $amount = $this->normalizePositiveMoney($amount, 'movementAmount');
        $reason = $this->normalizeReason($reason, 'movementReason', 5);
        $reference = $this->normalizeOptionalText($reference, 255, 'movementReference');

        return DB::transaction(function () use ($sessionId, $type, $amount, $reason, $reference, $actor, $operationId): CashMovement {
            $session = CashSession::query()->lockForUpdate()->findOrFail($sessionId);
            $this->authorizeSession($session, $actor);
            $this->ensureOpen($session);

            $existing = CashMovement::query()->where('operation_id', $operationId)->first();
            if ($existing) {
                abort_unless($existing->cash_session_id === $session->id, 409, 'La clave del movimiento pertenece a otra sesión.');

                return $existing;
            }

            $before = $this->expectedCash($session);
            $sensitiveWithdrawal = $type === 'withdrawal'
                && Decimal::compare($amount, (string) config('cash.withdrawal_approval_threshold'), 2) >= 0;
            if ($type === 'withdrawal') {
                AuditService::record('cash.withdrawal.requested', $session, [], [
                    'cash_session_id' => $session->id,
                    'amount' => $amount,
                    'reason' => $reason,
                    'requires_administrator' => $sensitiveWithdrawal,
                ], $actor->id);
            }
            if ($sensitiveWithdrawal && ! $actor->hasRole('Administrador')) {
                throw ValidationException::withMessages(['movementAmount' => 'Este retiro requiere autorización administrativa.']);
            }
            if (in_array($type, self::OUTFLOW_TYPES, true) && Decimal::compare($before, $amount, 2) < 0) {
                AuditService::record('cash.insufficient_balance', $session, [], [
                    'cash_session_id' => $session->id, 'requested_amount' => $amount, 'available_amount' => $before,
                ], $actor->id);
                throw ValidationException::withMessages(['movementAmount' => 'No existe suficiente efectivo para realizar este movimiento.']);
            }

            $movement = CashMovement::forceCreate([
                'cash_session_id' => $session->id,
                'user_id' => $actor->id,
                'operation_id' => $operationId,
                'type' => $type,
                'amount' => $amount,
                'reason' => $reason,
                'reference' => $reference,
                'approved_by' => $type === 'withdrawal' ? $actor->id : null,
                'approved_at' => $type === 'withdrawal' ? now() : null,
            ]);
            $after = in_array($type, self::INFLOW_TYPES, true)
                ? Decimal::add($before, $amount)
                : Decimal::subtract($before, $amount);

            AuditService::record('cash.movement.created', $movement, [], [
                'cash_register_id' => $session->cash_register_id,
                'cash_session_id' => $session->id,
                'movement_id' => $movement->id,
                'type' => $type,
                'amount' => $amount,
                'expected_before' => $before,
                'expected_after' => $after,
                'reason' => $reason,
                'reference' => $reference,
            ], $actor->id);
            if ($type === 'withdrawal') {
                AuditService::record('cash.withdrawal.approved', $movement, [], [
                    'cash_session_id' => $session->id,
                    'movement_id' => $movement->id,
                    'amount' => $amount,
                    'approved_by' => $actor->id,
                ], $actor->id);
            }

            return $movement;
        }, 3);
    }

    public function close(
        int $sessionId,
        array $quantities,
        ?string $differenceReason,
        User $actor,
        string $operationId,
    ): CashSession {
        $this->authorize($actor);
        $this->validateOperationId($operationId);

        return DB::transaction(function () use ($sessionId, $quantities, $differenceReason, $actor, $operationId): CashSession {
            $session = CashSession::query()->with('register')->lockForUpdate()->findOrFail($sessionId);
            $this->authorizeSession($session, $actor);

            if ($session->status === 'closed' && $session->closing_operation_id === $operationId) {
                return $session->load(['closingCount.lines.denomination']);
            }

            $this->ensureOpen($session);
            [$counted, $lines] = $this->calculateCount($session->register->currency_code, $quantities);
            $expected = $this->expectedCash($session);
            $difference = Decimal::subtract($counted, $expected);
            $differenceReason = $this->normalizeDifferenceReason($difference, $differenceReason);
            $absoluteDifference = ltrim($difference, '-');
            if (
                Decimal::compare($absoluteDifference, (string) config('cash.difference_approval_threshold'), 2) >= 0
                && ! $actor->hasRole('Administrador')
            ) {
                throw ValidationException::withMessages(['differenceReason' => 'Esta diferencia requiere autorización administrativa para cerrar.']);
            }

            $count = $this->persistCount(
                $session,
                'CLOSING',
                $operationId,
                $counted,
                $lines,
                $actor,
                $expected,
                $difference,
                $differenceReason,
            );

            $before = $session->getAttributes();
            $session->forceFill([
                'closing_operation_id' => $operationId,
                'expected_amount' => $expected,
                'closing_amount' => $counted,
                'difference' => $difference,
                'closing_notes' => $differenceReason,
                'status' => 'closed',
                'closed_at' => now(),
                'closed_by' => $actor->id,
            ])->save();

            $audit = [
                'cash_register_id' => $session->cash_register_id,
                'cash_session_id' => $session->id,
                'cash_count_id' => $count->id,
                'expected' => $expected,
                'counted' => $counted,
                'difference' => $difference,
                'difference_reason' => $differenceReason,
            ];
            AuditService::record('cash.closed', $session, $before, $audit, $actor->id);
            if (Decimal::compare($difference, '0.00', 2) !== 0) {
                AuditService::record('cash.difference.recorded', $session, [], $audit, $actor->id);
            }

            return $session->load(['register', 'openingCount.lines.denomination', 'closingCount.lines.denomination']);
        }, 3);
    }

    public function previewClose(int $sessionId, array $quantities, User $actor): array
    {
        $this->authorize($actor);

        return DB::transaction(function () use ($sessionId, $quantities, $actor): array {
            $session = CashSession::query()->with('register')->lockForUpdate()->findOrFail($sessionId);
            $this->authorizeSession($session, $actor);
            $this->ensureOpen($session);
            [$counted] = $this->calculateCount($session->register->currency_code, $quantities);
            $expected = $this->expectedCash($session);
            $difference = Decimal::subtract($counted, $expected);

            return [
                'expected' => $expected,
                'counted' => $counted,
                'difference' => $difference,
                'status' => Decimal::compare($difference, '0.00', 2) === 0
                    ? 'CUADRA'
                    : (Decimal::compare($difference, '0.00', 2) < 0 ? 'FALTANTE' : 'SOBRANTE'),
            ];
        }, 3);
    }

    public function activeSessionFor(User $actor, ?string $registerCode = null, bool $lock = false): ?CashSession
    {
        $query = CashSession::query()
            ->with('register')
            ->where('user_id', $actor->id)
            ->where('status', 'open');

        if ($registerCode !== null) {
            $query->whereHas('register', fn ($register) => $register->where('code', $registerCode));
        }
        if ($lock) {
            $query->lockForUpdate();
        }

        $sessions = $query->limit(2)->get();
        if ($sessions->count() > 1) {
            throw ValidationException::withMessages(['cash' => 'Existe más de una sesión activa para el usuario. Se requiere conciliación administrativa.']);
        }

        return $sessions->first();
    }

    public function expectedCash(CashSession $session): string
    {
        $income = (string) $session->movements()->whereIn('type', self::INFLOW_TYPES)->sum('amount');
        $outflow = (string) $session->movements()->whereIn('type', self::OUTFLOW_TYPES)->sum('amount');

        return Decimal::subtract(Decimal::add((string) $session->opening_amount, $income), $outflow);
    }

    public function movementBreakdown(CashSession $session): array
    {
        $result = [];
        foreach (array_merge(self::INFLOW_TYPES, self::OUTFLOW_TYPES) as $type) {
            $result[$type] = (string) $session->movements()->where('type', $type)->sum('amount');
        }
        $result['expected'] = $this->expectedCash($session);

        return $result;
    }

    private function persistCount(
        CashSession $session,
        string $type,
        string $operationId,
        string $total,
        array $lines,
        User $actor,
        ?string $expected = null,
        ?string $difference = null,
        ?string $differenceReason = null,
    ): CashCount {
        $count = CashCount::forceCreate([
            'cash_session_id' => $session->id,
            'operation_id' => $operationId,
            'type' => $type,
            'total' => $total,
            'expected_amount' => $expected,
            'difference' => $difference,
            'difference_reason' => $differenceReason,
            'performed_by' => $actor->id,
            'performed_at' => now(),
        ]);
        foreach ($lines as $line) {
            CashCountLine::forceCreate(['cash_count_id' => $count->id] + $line);
        }

        return $count;
    }

    private function calculateCount(string $currencyCode, array $quantities): array
    {
        $normalized = [];
        foreach ($quantities as $denominationId => $quantity) {
            $parsed = filter_var($quantity, FILTER_VALIDATE_INT);
            if ($parsed === false || $parsed < 0 || $parsed > (int) config('cash.max_denomination_quantity')) {
                throw ValidationException::withMessages(['denominationCounts' => 'Cada cantidad debe ser un entero dentro del límite permitido.']);
            }
            $normalized[(int) $denominationId] = $parsed;
        }

        $denominations = CashDenomination::query()
            ->where('currency_code', $currencyCode)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->lockForUpdate()
            ->get();
        if ($denominations->isEmpty()) {
            throw ValidationException::withMessages(['denominationCounts' => 'No existen denominaciones configuradas para esta moneda.']);
        }
        if (array_diff(array_keys($normalized), $denominations->modelKeys()) !== []) {
            throw ValidationException::withMessages(['denominationCounts' => 'El conteo contiene una denominación inválida o de otra moneda.']);
        }

        $total = '0.00';
        $lines = [];
        foreach ($denominations as $denomination) {
            $quantity = $normalized[$denomination->id] ?? 0;
            $subtotal = Money::fromUnitPrice((string) $denomination->value, $quantity, $currencyCode)->amount();
            $total = Decimal::add($total, $subtotal);
            $lines[] = [
                'cash_denomination_id' => $denomination->id,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];
        }

        return [$total, $lines];
    }

    private function authorize(User $actor): void
    {
        abort_unless($actor->is_active && $actor->hasAnyRole(['Administrador', 'Vendedor']), 403, 'No tienes permiso para operar caja.');
    }

    private function authorizeSession(CashSession $session, User $actor): void
    {
        abort_unless($session->user_id === $actor->id || $actor->hasRole('Administrador'), 403, 'No tienes permiso para operar esta sesión de caja.');
    }

    private function ensureOpen(CashSession $session): void
    {
        if ($session->status !== 'open') {
            throw ValidationException::withMessages(['cash' => 'La sesión fue cerrada mientras realizabas la operación.']);
        }
    }

    private function validateOperationId(string $operationId): void
    {
        if (! Str::isUuid($operationId)) {
            throw ValidationException::withMessages(['operationId' => 'La clave de operación no es válida.']);
        }
    }

    private function normalizePositiveMoney(string $amount, string $field): string
    {
        try {
            $amount = Decimal::parse($amount, Decimal::STORAGE_SCALE, false, $field);
        } catch (InvalidArgumentException) {
            throw ValidationException::withMessages([$field => 'El monto debe ser positivo y tener como máximo dos decimales.']);
        }
        if (Decimal::compare($amount, '0.00', 2) <= 0 || Decimal::compare($amount, '99999999.99', 2) > 0) {
            throw ValidationException::withMessages([$field => 'El monto debe ser positivo y tener como máximo dos decimales.']);
        }

        return $amount;
    }

    private function normalizeReason(string $reason, string $field, int $minimum): string
    {
        $reason = trim($reason);
        if (mb_strlen($reason) < $minimum || mb_strlen($reason) > 1000) {
            throw ValidationException::withMessages([$field => "El motivo debe contener entre {$minimum} y 1000 caracteres."]);
        }

        return $reason;
    }

    private function normalizeDifferenceReason(string $difference, ?string $reason): ?string
    {
        if (Decimal::compare($difference, '0.00', 2) === 0) {
            return $this->normalizeOptionalText($reason, 1000, 'differenceReason');
        }

        $reason = $this->normalizeReason((string) $reason, 'differenceReason', 10);
        if (in_array(mb_strtolower($reason), ['ok', 'error', '.', 'diferencia'], true)) {
            throw ValidationException::withMessages(['differenceReason' => 'Describe concretamente la causa de la diferencia.']);
        }

        return $reason;
    }

    private function normalizeOptionalText(?string $value, int $maximum, string $field): ?string
    {
        $value = $value === null ? null : trim($value);
        if ($value === '') {
            return null;
        }
        if ($value !== null && mb_strlen($value) > $maximum) {
            throw ValidationException::withMessages([$field => "El texto no puede exceder {$maximum} caracteres."]);
        }

        return $value;
    }
}
