<?php

namespace App\Livewire\Purchases;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\Purchases\PurchaseService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PurchasesPage extends Component
{
    use WithPagination;

    public $supplierId = '';

    public $productId = '';

    public $quantity = 1;

    public $unitCost = '';

    public $amountPaid = 0;

    public $dueDate = '';

    public $notes = '';

    public array $items = [];

    public function addItem(): void
    {
        $this->validate([
            'productId' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unitCost' => ['required', 'numeric', 'gt:0'],
        ]);
        $product = Product::findOrFail($this->productId);
        $index = collect($this->items)->search(fn ($item) => (int) $item['product_id'] === $product->id);
        if ($index !== false) {
            $this->items[$index]['quantity'] += (int) $this->quantity;
            $this->items[$index]['unit_cost'] = round((float) $this->unitCost, 2);
            $this->items[$index]['total'] = $this->items[$index]['quantity'] * $this->items[$index]['unit_cost'];
        } else {
            $this->items[] = [
                'product_id' => $product->id, 'code' => $product->code, 'name' => $product->name,
                'quantity' => (int) $this->quantity, 'unit_cost' => round((float) $this->unitCost, 2),
                'total' => (int) $this->quantity * round((float) $this->unitCost, 2),
            ];
        }
        $this->reset(['productId', 'unitCost']);
        $this->quantity = 1;
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save(PurchaseService $service): void
    {
        $this->validate(['supplierId' => ['required', 'integer', 'exists:suppliers,id'], 'amountPaid' => ['nullable', 'numeric', 'min:0'], 'dueDate' => ['nullable', 'date']]);
        $purchase = $service->create(Auth::user(), (int) $this->supplierId, $this->items, $this->amountPaid, $this->dueDate ?: null, $this->notes ?: null);
        $this->reset(['supplierId', 'amountPaid', 'dueDate', 'notes', 'items']);
        $this->dispatch('purchaseSaved', number: $purchase->purchase_number);
    }

    public function getTotalProperty(): float
    {
        return round(collect($this->items)->sum('total'), 2);
    }

    public function render()
    {
        return view('livewire.purchases.index', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
            'purchases' => Purchase::with(['supplier', 'user'])->latest('purchase_date')->paginate(10),
        ]);
    }
}
