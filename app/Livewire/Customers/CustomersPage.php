<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\CustomerType;
use Livewire\Component;
use Livewire\WithPagination;

class CustomersPage extends Component
{
    use WithPagination;

    public Customer $customer;

    public $customer_types;

    public bool $isEditing = false;

    public string $search = '';

    public string $typeFilter = 'all';

    public array $cities = ['Managua', 'León', 'Granada', 'Masaya', 'Chinandega', 'Estelí', 'Rivas', 'Bluefields', 'Jinotega', 'Matagalpa', 'Carazo', 'Boaco', 'Nueva Segovia', 'Chontales', 'Rio San Juan'];

    protected $rules = [
        'customer.dni_ruc' => 'required|string|max:255', 'customer.name' => 'required|string|max:255',
        'customer.email' => 'required|email|max:255', 'customer.phone' => 'required|string|max:255',
        'customer.address' => 'required|string|max:255', 'customer.city' => 'required|string|max:255',
        'customer.customer_type_id' => 'required|exists:customer_types,id',
    ];

    public function mount(): void
    {
        $this->customer_types = CustomerType::orderBy('name')->get();
        $this->resetCustomer();
    }

    public function updatedSearch(): void
    {
        $this->resetPage('pageCustomer');
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage('pageCustomer');
    }

    public function render()
    {
        $customers = Customer::query()->with('customerType')
            ->when($this->search, fn ($query) => $query->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")->orWhere('dni_ruc', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")->orWhere('phone', 'like', "%{$this->search}%");
            }))->when($this->typeFilter !== 'all', fn ($query) => $query->where('customer_type_id', $this->typeFilter))
            ->orderBy('name')->paginate(10, pageName: 'pageCustomer');

        return view('livewire.customers.index', compact('customers'));
    }

    public function create(): void
    {
        $this->resetCustomer();
        $this->dispatch('open-modal', 'modal-form-customer');
    }

    public function update(int $id): void
    {
        $this->isEditing = true;
        $this->customer = Customer::findOrFail($id);
        $this->resetValidation();
        $this->dispatch('open-modal', 'modal-form-customer');
    }

    public function destroy(int $id): void
    {
        Customer::findOrFail($id)->delete();
        $this->dispatch('customerDeleted');
    }

    public function save(): void
    {
        $this->validate();
        $this->customer->save();
        $this->dispatch('close-modal', 'modal-form-customer');
        $this->dispatch('customerSaved');
        $this->resetCustomer();
    }

    private function resetCustomer(): void
    {
        $this->customer = new Customer(['city' => 'Managua', 'customer_type_id' => $this->customer_types->first()?->id]);
        $this->isEditing = false;
        $this->resetValidation();
    }
}
