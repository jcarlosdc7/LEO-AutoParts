<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;

class SalesHistoryPage extends Component
{
    use WithPagination;

    public $selectedSale;

    public $saleDetails = [];

    protected $listeners = ['showDetails'];

    public $sortColumn = 'sale_date'; // Columna predeterminada para ordenar

    public $sortDirection = 'desc';  // Dirección predeterminada (descendente)

    // Método para cambiar la columna y la dirección
    public function sortBy($column)
    {
        if ($this->sortColumn === $column) {
            // Si se hace clic en la misma columna, alternar la dirección
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // Si se selecciona otra columna, usar dirección ascendente por defecto
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function view($id)
    {
        // dd($id);
        $this->selectedSale = Sale::with('customer', 'user', 'paymentMethod', 'saleDetails.product')->findOrFail($id);
        // dd($this->selectedSale);
        $this->saleDetails = $this->selectedSale->saleDetails;
        // dd($this->saleDetails);
        $this->dispatch('open-modal', 'modal-sale-detail');
    }

    public function destroy($id)
    {
        Sale::destroy($id);
    }

    public function render()
    {
        $sales = Sale::with(['customer', 'user', 'paymentMethod'])->orderBy($this->sortColumn, $this->sortDirection)->paginate(10, pageName: 'pageSales');

        return view('livewire.sales.history', compact('sales'));
    }
}
