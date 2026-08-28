<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\CashDenomination;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\User;
use App\Services\CashService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class CashPanel extends Component
{
    use WithPagination;

    public string $registerCode = '';

    public array $denominationValues = [];

    public array $openingCounts = [];

    public array $closingCounts = [];

    public string $openingNotes = '';

    public string $movementType = 'income';

    public string $movementAmount = '';

    public string $movementReason = '';

    public string $movementReference = '';

    public string $differenceReason = '';

    public string $openingOperationId = '';

    public string $movementOperationId = '';

    public string $closingOperationId = '';

    public ?int $selectedHistorySessionId = null;

    public string $historyStatus = 'all';

    public string $historyFrom = '';

    public string $historyTo = '';

    public string $historyDifference = 'all';

    public string $historyRegister = 'all';

    public string $historyUser = 'all';

    public ?array $closedSummary = null;

    public ?array $closingPreview = null;

    public function mount(CashService $cash): void
    {
        $this->ensureCashier();
        $active = $cash->activeSessionFor(Auth::user());
        $this->registerCode = $active?->register?->code ?? (string) config('cash.default_register_code');
        $this->refreshOperationIds();
        $this->loadDenominations();
    }

    public function updatedRegisterCode(): void
    {
        $this->loadDenominations();
        $this->resetPage(pageName: 'movementsPage');
    }

    public function updatedHistoryStatus(): void
    {
        $this->resetPage(pageName: 'historyPage');
    }

    public function updatedHistoryFrom(): void
    {
        $this->resetPage(pageName: 'historyPage');
    }

    public function updatedHistoryTo(): void
    {
        $this->resetPage(pageName: 'historyPage');
    }

    public function updatedHistoryDifference(): void
    {
        $this->resetPage(pageName: 'historyPage');
    }

    public function updatedHistoryRegister(): void
    {
        $this->resetPage(pageName: 'historyPage');
    }

    public function updatedHistoryUser(): void
    {
        $this->resetPage(pageName: 'historyPage');
    }

    public function updatedClosingCounts(): void
    {
        $this->closingPreview = null;
    }

    public function showOpening(): void
    {
        $this->ensureCashier();
        $this->loadDenominations();
        $this->dispatch('open-modal', 'cash-opening');
    }

    public function openSession(CashService $cash): void
    {
        $this->ensureCashier();
        $cash->open($this->registerCode, $this->openingCounts, $this->openingNotes, Auth::user(), $this->openingOperationId);
        $this->openingNotes = '';
        $this->openingOperationId = (string) Str::uuid();
        $this->loadDenominations();
        $this->dispatch('close-modal', 'cash-opening');
    }

    public function showMovement(): void
    {
        $this->ensureCashier();
        $this->dispatch('open-modal', 'cash-movement');
    }

    public function addMovement(CashService $cash): void
    {
        $session = $this->requireCurrentSession($cash);
        $cash->recordMovement(
            $session->id,
            $this->movementType,
            $this->movementAmount,
            $this->movementReason,
            $this->movementReference ?: null,
            Auth::user(),
            $this->movementOperationId,
        );
        $this->reset(['movementAmount', 'movementReason', 'movementReference']);
        $this->movementOperationId = (string) Str::uuid();
        $this->dispatch('close-modal', 'cash-movement');
    }

    public function showClosing(CashService $cash): void
    {
        $this->requireCurrentSession($cash);
        $this->loadDenominations();
        $this->differenceReason = '';
        $this->closedSummary = null;
        $this->closingPreview = null;
        $this->dispatch('open-modal', 'cash-closing');
    }

    public function reviewClosing(CashService $cash): void
    {
        $session = $this->requireCurrentSession($cash);
        $this->closingPreview = $cash->previewClose($session->id, $this->closingCounts, Auth::user());
    }

    public function closeSession(CashService $cash): void
    {
        abort_unless($this->closingPreview !== null, 422, 'Debe revisar el conteo antes de confirmar el cierre.');
        $session = $this->requireCurrentSession($cash);
        $closed = $cash->close(
            $session->id,
            $this->closingCounts,
            $this->differenceReason ?: null,
            Auth::user(),
            $this->closingOperationId,
        );
        $this->closedSummary = [
            'expected' => (string) $closed->expected_amount,
            'counted' => (string) $closed->closing_amount,
            'difference' => (string) $closed->difference,
            'status' => bccomp((string) $closed->difference, '0.00', 2) === 0
                ? 'CUADRA'
                : (bccomp((string) $closed->difference, '0.00', 2) < 0 ? 'FALTANTE' : 'SOBRANTE'),
        ];
        $this->closingOperationId = (string) Str::uuid();
        $this->dispatch('close-modal', 'cash-closing');
        $this->dispatch('open-modal', 'cash-closing-result');
    }

    public function clearOpeningCount(): void
    {
        $this->openingCounts = array_fill_keys(array_keys($this->denominationValues), 0);
    }

    public function clearClosingCount(): void
    {
        $this->closingCounts = array_fill_keys(array_keys($this->denominationValues), 0);
    }

    public function viewSession(int $sessionId): void
    {
        $session = CashSession::query()->findOrFail($sessionId);
        $this->authorizeHistoricalSession($session);
        $this->selectedHistorySessionId = $session->id;
        $this->dispatch('open-modal', 'cash-session-detail');
    }

    public function getOpeningTotalProperty(): string
    {
        return $this->countTotal($this->openingCounts);
    }

    public function getClosingTotalProperty(): string
    {
        return $this->countTotal($this->closingCounts);
    }

    public function getProjectedBalanceProperty(): ?string
    {
        $session = app(CashService::class)->activeSessionFor(Auth::user(), $this->registerCode);
        if (! $session || ! preg_match('/^\d{1,10}(?:\.\d{1,2})?$/', trim($this->movementAmount))) {
            return null;
        }

        $current = app(CashService::class)->expectedCash($session);

        return in_array($this->movementType, CashService::INFLOW_TYPES, true)
            ? bcadd($current, $this->movementAmount, 2)
            : bcsub($current, $this->movementAmount, 2);
    }

    public function lineSubtotal(int $denominationId, string $context): string
    {
        $quantity = $context === 'opening'
            ? ($this->openingCounts[$denominationId] ?? 0)
            : ($this->closingCounts[$denominationId] ?? 0);
        $quantity = filter_var($quantity, FILTER_VALIDATE_INT);

        return $quantity === false || $quantity < 0
            ? '0.00'
            : bcmul($this->denominationValues[$denominationId] ?? '0.00', (string) $quantity, 2);
    }

    public function render(CashService $cash)
    {
        $session = $cash->activeSessionFor(Auth::user(), $this->registerCode);
        $registers = CashRegister::query()->where('is_active', true)->orderBy('name')->get();
        $denominations = CashDenomination::query()
            ->where('currency_code', $this->currentCurrency($registers))
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        $breakdown = $session ? $cash->movementBreakdown($session) : [];
        $movements = $session
            ? $session->movements()->with('user')->latest()->paginate(15, pageName: 'movementsPage')
            : null;

        $historyQuery = CashSession::query()
            ->with(['register', 'user'])
            ->when(! Auth::user()->hasRole('Administrador'), fn ($query) => $query->where('user_id', Auth::id()))
            ->when($this->historyStatus !== 'all', fn ($query) => $query->where('status', $this->historyStatus))
            ->when($this->historyFrom !== '', fn ($query) => $query->whereDate('opened_at', '>=', $this->historyFrom))
            ->when($this->historyTo !== '', fn ($query) => $query->whereDate('opened_at', '<=', $this->historyTo))
            ->when($this->historyDifference === 'with', fn ($query) => $query->where('difference', '<>', '0.00'))
            ->when($this->historyDifference === 'without', fn ($query) => $query->where('difference', '0.00'))
            ->when($this->historyRegister !== 'all', fn ($query) => $query->where('cash_register_id', $this->historyRegister))
            ->when($this->historyUser !== 'all', fn ($query) => $query->where('user_id', $this->historyUser))
            ->latest('opened_at');

        $selectedHistorySession = $this->selectedHistorySessionId
            ? CashSession::with([
                'register', 'user', 'closingUser', 'openingCount.lines.denomination',
                'closingCount.lines.denomination', 'movements.user', 'sales.customer', 'refunds.paymentMethod',
            ])->find($this->selectedHistorySessionId)
            : null;
        if ($selectedHistorySession) {
            $this->authorizeHistoricalSession($selectedHistorySession);
        }
        $selectedAudit = $selectedHistorySession
            ? AuditLog::query()
                ->where(function ($query) use ($selectedHistorySession): void {
                    $query->where(function ($query) use ($selectedHistorySession): void {
                        $query->where('auditable_type', CashSession::class)
                            ->where('auditable_id', $selectedHistorySession->id);
                    })->orWhere('new_values->cash_session_id', $selectedHistorySession->id);
                })
                ->latest()
                ->limit(50)
                ->get()
            : collect();

        return view('livewire.lwCash.cash-panel', [
            'session' => $session,
            'registers' => $registers,
            'denominations' => $denominations,
            'banknotes' => $denominations->where('type', 'BANKNOTE'),
            'coins' => $denominations->where('type', 'COIN'),
            'breakdown' => $breakdown,
            'movements' => $movements,
            'history' => $historyQuery->paginate(10, pageName: 'historyPage'),
            'historyUsers' => Auth::user()->hasRole('Administrador')
                ? User::query()->whereHas('role', fn ($role) => $role->whereIn('name', ['Administrador', 'Vendedor']))->orderBy('name')->get()
                : collect([Auth::user()]),
            'selectedHistorySession' => $selectedHistorySession,
            'selectedAudit' => $selectedAudit,
            'currencySymbol' => config('cash.currency_symbol'),
        ]);
    }

    private function requireCurrentSession(CashService $cash): CashSession
    {
        $this->ensureCashier();
        $session = $cash->activeSessionFor(Auth::user(), $this->registerCode);
        if (! $session) {
            throw ValidationException::withMessages(['cash' => 'Debe abrir la caja seleccionada primero.']);
        }

        return $session;
    }

    private function loadDenominations(): void
    {
        $register = CashRegister::query()->where('code', $this->registerCode)->where('is_active', true)->first();
        $currency = $register?->currency_code ?? (string) config('cash.currency_code');
        $denominations = CashDenomination::query()
            ->where('currency_code', $currency)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $this->denominationValues = $denominations->pluck('value', 'id')->map(fn ($value) => (string) $value)->all();
        $this->openingCounts = array_replace(array_fill_keys($denominations->modelKeys(), 0), $this->openingCounts);
        $this->closingCounts = array_replace(array_fill_keys($denominations->modelKeys(), 0), $this->closingCounts);
    }

    private function countTotal(array $counts): string
    {
        $total = '0.00';
        foreach ($this->denominationValues as $id => $value) {
            $quantity = filter_var($counts[$id] ?? 0, FILTER_VALIDATE_INT);
            if ($quantity !== false && $quantity >= 0) {
                $total = bcadd($total, bcmul($value, (string) $quantity, 2), 2);
            }
        }

        return $total;
    }

    private function currentCurrency($registers): string
    {
        return (string) ($registers->firstWhere('code', $this->registerCode)?->currency_code ?? config('cash.currency_code'));
    }

    private function refreshOperationIds(): void
    {
        $this->openingOperationId = (string) Str::uuid();
        $this->movementOperationId = (string) Str::uuid();
        $this->closingOperationId = (string) Str::uuid();
    }

    private function authorizeHistoricalSession(CashSession $session): void
    {
        abort_unless(Auth::user()->hasRole('Administrador') || $session->user_id === Auth::id(), 403);
    }

    private function ensureCashier(): void
    {
        abort_unless(Auth::user()?->is_active && Auth::user()?->hasAnyRole(['Administrador', 'Vendedor']), 403);
    }
}
