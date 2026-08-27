<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\CustomerType;
use Livewire\Component;
use Livewire\WithPagination;

class CustomersPanel extends Component
{
    use WithPagination;

    public Customer $customer;

    public CustomerType $customerType;

    // public $customers;
    public $customer_types;

    public $modal = false;

    public $isEditing = false;

    public $cities = [
        'Managua',
        'León',
        'Granada',
        'Masaya',
        'Chinandega',
        'Estelí',
        'Rivas',
        'Bluefields',
        'Jinotega',
        'Matagalpa',
        'Carazo',
        'Boaco',
        'Nueva Segovia',
        'Chontales',
        'Rio San Juan',
    ];

    // ========================== VALIDACION ==========================
    protected $rules = [
        'customer.dni_ruc' => 'required|string|max:255',
        'customer.name' => 'required|string|max:255',
        'customer.email' => 'required|string|max:255',
        'customer.phone' => 'required|string|max:255',
        'customer.address' => 'required|string|max:255',
        'customer.city' => 'required|string|max:255',
        'customer.customer_type_id' => 'required',
    ];

    protected $validationAttributes = [
        'customer.dni_ruc' => 'DNI/RUC',
        'customer.name' => 'Nombre',
        'customer.email' => 'Correo electrónico',
        'customer.phone' => 'Teléfono',
        'customer.address' => 'Dirección',
        'customer.city' => 'Ciudad',
        'customer.customer_type_id' => 'Tipo de cliente',
    ];

    public function mount()
    {
        $this->customer = new Customer;

        // $this->customers = Customer::all();
        $this->customer_types = CustomerType::all();
    }

    public function render()
    {
        $customers = Customer::where('is_active', true)->paginate(10, pageName: 'pageCustomer');

        // EN CASO DE QUE LA ULTIMA PAGINA SE QUEDE SIN REGISTROS PARA RENDERIZAR
        if ($customers->count() === 0) {
            $this->previousPage(pageName: 'pageCustomer');
            $customers = Customer::where('is_active', true)->paginate(10, pageName: 'pageCustomer');
        }

        return view('livewire.lwCustomers.customers-panel', compact('customers'));
    }

    // ========================== CRUD ==========================
    public function create()
    {
        $this->resetValidation();
        $this->dispatch('open-modal', 'modal-form-customer');
    }

    public function update($id)
    {
        $this->isEditing = true;
        $this->customer = Customer::find($id);

        $this->resetValidation();
        $this->dispatch('open-modal', 'modal-form-customer');
    }

    public function destroy($id)
    {
        Customer::findOrFail($id)->update(['is_active' => false]);
        // $this->customers = Customer::all();
    }

    public function save()
    {
        if ($this->customer->customer_type_id == null) {
            $this->customer->customer_type_id = 1;
        }

        if ($this->customer->city == null) {
            $this->customer->city = 'Managua';
        }

        $this->validate();
        $this->customer->save();

        $this->dispatch('close-modal', 'modal-form-customer');

        // $this->customers = Customer::all();
        $this->customer = new Customer;
        $this->isEditing = false;

    }

    // ========================== ADICIONALES ==========================
    public function getCustomerType($id)
    {
        $this->customerType = CustomerType::find($id);

        return $this->customerType->name;
    }
}
