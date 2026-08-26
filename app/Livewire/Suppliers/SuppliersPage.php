<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class SuppliersPage extends Component
{
    use WithPagination;

    public Supplier $supplier;

    public bool $isEditing = false;

    public string $search = '';

    protected $rules = ['supplier.name' => 'required|string|max:255', 'supplier.contact' => 'required|string|max:255', 'supplier.phone' => 'required|string|max:15', 'supplier.address' => 'required|string|max:255'];

    public function mount(): void
    {
        $this->supplier = new Supplier;
    }

    public function updatedSearch(): void
    {
        $this->resetPage('pageSupplier');
    }

    public function render()
    {
        $suppliers = Supplier::query()->when($this->search, fn ($query) => $query->where(function ($query) {
            $query->where('name', 'like', "%{$this->search}%")->orWhere('contact', 'like', "%{$this->search}%")->orWhere('phone', 'like', "%{$this->search}%");
        }))->orderBy('name')->paginate(10, pageName: 'pageSupplier');

        return view('livewire.suppliers.index', compact('suppliers'));
    }

    public function create(): void
    {
        $this->supplier = new Supplier;
        $this->isEditing = false;
        $this->resetValidation();
        $this->dispatch('open-modal', 'modal-form-supplier');
    }

    public function update(int $id): void
    {
        $this->isEditing = true;
        $this->supplier = Supplier::findOrFail($id);
        $this->resetValidation();
        $this->dispatch('open-modal', 'modal-form-supplier');
    }

    public function destroy(int $id): void
    {
        Supplier::findOrFail($id)->delete();
        $this->dispatch('supplierDeleted');
    }

    public function save(): void
    {
        $this->validate();
        $this->supplier->save();
        $this->dispatch('close-modal', 'modal-form-supplier');
        $this->dispatch('supplierSaved');
        $this->supplier = new Supplier;
        $this->isEditing = false;
    }
}
