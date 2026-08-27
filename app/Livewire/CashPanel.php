<?php

namespace App\Livewire;

use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CashPanel extends Component
{
    public $openingAmount = 0;
    public $closingAmount;
    public $openingNotes = '';
    public $closingNotes = '';
    public $movementType = 'income';
    public $movementAmount;
    public $movementReason = '';

    public function mount(): void
    {
        $this->ensureCashier();
    }

    public function openSession(): void
    {
        $this->ensureCashier();
        $data = $this->validate([
            'openingAmount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'openingNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data) {
            $register = CashRegister::where('is_active', true)->lockForUpdate()->firstOrFail();
            if (CashSession::where('user_id', Auth::id())->where('status', 'open')->exists()) {
                throw ValidationException::withMessages(['openingAmount' => 'Ya tiene una caja abierta.']);
            }
            if (CashSession::where('cash_register_id', $register->id)->where('status', 'open')->exists()) {
                throw ValidationException::withMessages(['openingAmount' => 'La caja ya está ocupada por otro usuario.']);
            }

            $session = CashSession::create([
                'cash_register_id' => $register->id,
                'user_id' => Auth::id(),
                'opening_amount' => $data['openingAmount'],
                'opening_notes' => $data['openingNotes'] ?: null,
                'status' => 'open',
                'opened_at' => now(),
            ]);
            AuditService::record('cash.opened', $session, [], $session->getAttributes());
        });
    }

    public function addMovement(): void
    {
        $this->ensureCashier();
        $data = $this->validate([
            'movementType' => ['required', Rule::in(['income', 'expense', 'withdrawal'])],
            'movementAmount' => ['required', 'numeric', 'gt:0', 'max:999999.99'],
            'movementReason' => ['required', 'string', 'max:255'],
        ]);
        $session = $this->currentSession();
        abort_unless($session, 422, 'Debe abrir la caja primero.');

        $movement = CashMovement::create([
            'cash_session_id' => $session->id,
            'user_id' => Auth::id(),
            'type' => $data['movementType'],
            'amount' => $data['movementAmount'],
            'reason' => $data['movementReason'],
        ]);
        AuditService::record('cash.movement.created', $movement, [], $movement->getAttributes());
        $this->reset(['movementAmount', 'movementReason']);
    }

    public function closeSession(): void
    {
        $this->ensureCashier();
        $data = $this->validate([
            'closingAmount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'closingNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data) {
            $session = CashSession::where('user_id', Auth::id())->where('status', 'open')->lockForUpdate()->firstOrFail();
            $income = (float) $session->movements()->whereIn('type', ['sale', 'income'])->sum('amount');
            $outflow = (float) $session->movements()->whereIn('type', ['expense', 'withdrawal', 'refund'])->sum('amount');
            $expected = round((float) $session->opening_amount + $income - $outflow, 2);
            $closing = round((float) $data['closingAmount'], 2);
            $old = $session->getAttributes();
            $session->update([
                'expected_amount' => $expected,
                'closing_amount' => $closing,
                'difference' => round($closing - $expected, 2),
                'closing_notes' => $data['closingNotes'] ?: null,
                'status' => 'closed',
                'closed_at' => now(),
                'closed_by' => Auth::id(),
            ]);
            AuditService::record('cash.closed', $session, $old, $session->getAttributes());
        });
        $this->reset(['closingAmount', 'closingNotes']);
    }

    public function render()
    {
        $session = $this->currentSession();
        return view('livewire.lwCash.cash-panel', [
            'session' => $session,
            'movements' => $session?->movements()->latest()->limit(20)->get() ?? collect(),
        ]);
    }

    private function currentSession(): ?CashSession
    {
        return CashSession::with('register')->where('user_id', Auth::id())->where('status', 'open')->latest('opened_at')->first();
    }

    private function ensureCashier(): void
    {
        abort_unless(Auth::user()?->is_active && Auth::user()?->hasAnyRole(['Administrador', 'Vendedor']), 403);
    }
}
