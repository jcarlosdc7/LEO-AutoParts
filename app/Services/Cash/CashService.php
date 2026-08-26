<?php

namespace App\Services\Cash;

use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashService
{
    public function open(User $user, mixed $openingAmount, ?string $notes = null): CashSession
    {
        $amount = round((float) $openingAmount, 2);
        if ($amount < 0) {
            throw ValidationException::withMessages(['openingAmount' => 'El fondo inicial no puede ser negativo.']);
        }

        return DB::transaction(function () use ($user, $amount, $notes) {
            if (CashSession::where('status', 'open')->lockForUpdate()->exists()) {
                throw ValidationException::withMessages(['cash' => 'Ya existe una caja abierta.']);
            }

            return CashSession::create(['opened_by' => $user->id, 'opening_amount' => $amount, 'opened_at' => now(), 'status' => 'open', 'notes' => $notes]);
        }, 3);
    }

    public function addMovement(User $user, string $type, mixed $amount, string $description): CashMovement
    {
        $value = round((float) $amount, 2);
        if (! in_array($type, ['income', 'expense'], true) || $value <= 0 || trim($description) === '') {
            throw ValidationException::withMessages(['movement' => 'Indique tipo, importe positivo y descripción.']);
        }

        return DB::transaction(function () use ($user, $type, $value, $description) {
            $session = CashSession::where('status', 'open')->lockForUpdate()->first();
            if (! $session) {
                throw ValidationException::withMessages(['cash' => 'Debe abrir la caja antes de registrar movimientos.']);
            }

            return CashMovement::create(['cash_session_id' => $session->id, 'type' => $type, 'amount' => $value, 'description' => trim($description), 'user_id' => $user->id, 'occurred_at' => now()]);
        }, 3);
    }

    public function close(User $user, mixed $closingAmount, ?string $notes = null): CashSession
    {
        $counted = round((float) $closingAmount, 2);
        if ($counted < 0) {
            throw ValidationException::withMessages(['closingAmount' => 'El efectivo contado no puede ser negativo.']);
        }

        return DB::transaction(function () use ($user, $counted, $notes) {
            $session = CashSession::where('status', 'open')->lockForUpdate()->first();
            if (! $session) {
                throw ValidationException::withMessages(['cash' => 'No existe una caja abierta.']);
            }
            $income = (float) $session->movements()->where('type', 'income')->sum('amount');
            $expenses = (float) $session->movements()->where('type', 'expense')->sum('amount');
            $expected = round((float) $session->opening_amount + $income - $expenses, 2);
            $session->update(['closed_by' => $user->id, 'expected_amount' => $expected, 'closing_amount' => $counted, 'difference' => $counted - $expected, 'closed_at' => now(), 'status' => 'closed', 'notes' => $notes ?: $session->notes]);

            return $session->fresh(['openedBy', 'closedBy', 'movements']);
        }, 3);
    }
}
