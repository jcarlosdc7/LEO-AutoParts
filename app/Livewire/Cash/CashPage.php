<?php

namespace App\Livewire\Cash;

use App\Models\CashSession;
use App\Services\Cash\CashService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CashPage extends Component
{
    use WithPagination;

    public $openingAmount = 0;

    public $openingNotes = '';

    public $movementType = 'income';

    public $movementAmount = '';

    public $movementDescription = '';

    public $closingAmount = '';

    public $closingNotes = '';

    public function openCash(CashService $service): void
    {
        $this->validate(['openingAmount' => ['required', 'numeric', 'min:0']]);
        $service->open(Auth::user(), $this->openingAmount, $this->openingNotes ?: null);
        $this->reset(['openingAmount', 'openingNotes']);
        $this->dispatch('cashUpdated', message: 'Caja abierta correctamente');
    }

    public function addMovement(CashService $service): void
    {
        $this->validate(['movementType' => ['required', 'in:income,expense'], 'movementAmount' => ['required', 'numeric', 'gt:0'], 'movementDescription' => ['required', 'string', 'max:255']]);
        $service->addMovement(Auth::user(), $this->movementType, $this->movementAmount, $this->movementDescription);
        $this->reset(['movementAmount', 'movementDescription']);
        $this->movementType = 'income';
        $this->dispatch('cashUpdated', message: 'Movimiento registrado');
    }

    public function closeCash(CashService $service): void
    {
        $this->validate(['closingAmount' => ['required', 'numeric', 'min:0']]);
        $service->close(Auth::user(), $this->closingAmount, $this->closingNotes ?: null);
        $this->reset(['closingAmount', 'closingNotes']);
        $this->dispatch('cashUpdated', message: 'Caja cerrada correctamente');
    }

    public function render()
    {
        $current = CashSession::with(['openedBy', 'movements.user'])->where('status', 'open')->first();
        $income = $current ? (float) $current->movements->where('type', 'income')->sum('amount') : 0;
        $expenses = $current ? (float) $current->movements->where('type', 'expense')->sum('amount') : 0;

        return view('livewire.cash.index', [
            'current' => $current, 'income' => $income, 'expenses' => $expenses,
            'expected' => $current ? (float) $current->opening_amount + $income - $expenses : 0,
            'sessions' => CashSession::with(['openedBy', 'closedBy'])->latest('opened_at')->paginate(10),
        ]);
    }
}
