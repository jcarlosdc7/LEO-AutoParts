<?php

namespace App\Livewire\Inventory;

use App\Models\InventoryMovement;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class KardexPage extends Component
{
    use WithPagination;

    public $productId = '';

    public $type = '';

    public $dateFrom = '';

    public $dateTo = '';

    public function updated($property): void
    {
        if (in_array($property, ['productId', 'type', 'dateFrom', 'dateTo'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $query = InventoryMovement::with(['product', 'user'])->latest('occurred_at');
        $query->when($this->productId, fn ($q) => $q->where('product_id', $this->productId));
        $query->when($this->type, fn ($q) => $q->where('type', $this->type));
        $query->when($this->dateFrom, fn ($q) => $q->whereDate('occurred_at', '>=', $this->dateFrom));
        $query->when($this->dateTo, fn ($q) => $q->whereDate('occurred_at', '<=', $this->dateTo));

        return view('livewire.inventory.kardex', [
            'products' => Product::orderBy('name')->get(['id', 'code', 'name']),
            'movements' => $query->paginate(15),
            'totalEntries' => (clone $query)->where('quantity', '>', 0)->sum('quantity'),
            'totalExits' => abs((int) (clone $query)->where('quantity', '<', 0)->sum('quantity')),
        ]);
    }
}
