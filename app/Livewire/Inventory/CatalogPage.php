<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class CatalogPage extends Component
{
    use WithPagination;

    public string $search = '';

    public string $availability = 'all';

    public function updatedSearch(): void
    {
        $this->resetPage('pageCatalog');
    }

    public function updatedAvailability(): void
    {
        $this->resetPage('pageCatalog');
    }

    public function render()
    {
        $products = Product::query()->where('is_active', true)
            ->when($this->search, fn ($query) => $query->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")->orWhere('code', 'like', "%{$this->search}%")
                    ->orWhere('brand', 'like', "%{$this->search}%")->orWhere('model', 'like', "%{$this->search}%");
            }))->when($this->availability === 'available', fn ($query) => $query->where('stock', '>', 0))
            ->when($this->availability === 'low', fn ($query) => $query->whereColumn('stock', '<=', 'min_stock'))
            ->orderBy('name')->paginate(12, pageName: 'pageCatalog');

        return view('livewire.inventory.catalog', compact('products'));
    }
}
