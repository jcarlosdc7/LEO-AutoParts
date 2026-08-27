<?php

namespace App\Livewire;

use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class SalesHistoryPanel extends Component
{
    use WithPagination;

    public $selectedSale;

    public $saleDetails = [];

    public ?int $saleToVoidId = null;

    public string $voidReason = '';

    protected $listeners = ['showDetails'];

    public $sortColumn = 'sale_date'; // Columna predeterminada para ordenar

    public $sortDirection = 'desc';  // Dirección predeterminada (descendente)

    public function sortBy(string $column): void
    {
        $allowedColumns = ['id', 'customer_id', 'user_id', 'total', 'sale_date', 'status'];
        abort_unless(in_array($column, $allowedColumns, true), 422, 'Columna de ordenamiento inválida.');

        if ($this->sortColumn === $column) {
            // Si se hace clic en la misma columna, alternar la dirección
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // Si se selecciona otra columna, usar dirección ascendente por defecto
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function view(int $id): void
    {
        $this->selectedSale = Sale::with('customer', 'user', 'paymentMethod', 'saleDetails.product')->findOrFail($id);
        $this->saleDetails = $this->selectedSale->saleDetails;
        $this->dispatch('open-modal', 'modal-sale-detail');
    }

    public function requestVoid(int $id): void
    {
        $this->ensureAdministrator();

        $sale = Sale::query()->findOrFail($id);
        abort_unless($sale->status === 'completed', 422, 'La venta ya no puede anularse.');

        $this->resetValidation();
        $this->saleToVoidId = $sale->id;
        $this->voidReason = '';
        $this->dispatch('open-modal', 'modal-void-sale');
    }

    public function voidSale(SaleService $saleService): void
    {
        $this->ensureAdministrator();

        $data = $this->validate([
            'saleToVoidId' => ['required', 'integer', Rule::exists('sales', 'id')],
            'voidReason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $saleService->void($data['saleToVoidId'], $data['voidReason'], Auth::user());

        $this->reset(['saleToVoidId', 'voidReason']);
        $this->dispatch('close-modal', 'modal-void-sale');
        $this->dispatch('sale-voided');
    }

    public function render()
    {
        $sales = Sale::with(['customer', 'user', 'paymentMethod'])->orderBy($this->sortColumn, $this->sortDirection)->paginate(10, pageName: 'pageSales');

        return view('livewire.lwSalesHistory.sales-history-panel', compact('sales'));
    }

    private function ensureAdministrator(): void
    {
        abort_unless(Auth::user()?->is_active && Auth::user()?->hasRole('Administrador'), 403);
    }
}
