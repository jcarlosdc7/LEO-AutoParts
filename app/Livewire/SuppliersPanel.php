<?php

namespace App\Livewire;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class SuppliersPanel extends Component
{
    protected $listeners = ['confirmado' => 'confirmadoAccionar'];

    use WithPagination;

    public Supplier $supplier;

    // public $suppliers;
    public $idForDeleting = null;

    public $modal = false;

    public $isEditing = false;

    // ========================== VALIDACION ==========================
    protected $rules = [
        'supplier.name' => 'required|string|max:255',
        'supplier.contact' => 'required|string|max:255',
        'supplier.phone' => 'required|string|max:15',
        'supplier.address' => 'required|string|max:255',
    ];

    protected $validationAttributes = [
        'supplier.name' => 'Nombre',
        'supplier.contact' => 'Contacto',
        'supplier.phone' => 'Teléfono',
        'supplier.address' => 'Dirección',
    ];

    public function mount()
    {
        $this->supplier = new Supplier;

        // $this->suppliers = Supplier::All();
    }

    public function render()
    {
        $suppliers = Supplier::where('is_active', true)->paginate(10, pageName: 'pageSupplier');

        // EN CASO DE QUE LA ULTIMA PAGINA SE QUEDE SIN REGISTROS PARA RENDERIZAR
        if ($suppliers->count() === 0) {
            $this->previousPage(pageName: 'pageSupplier');
            $suppliers = Supplier::where('is_active', true)->paginate(10, pageName: 'pageSupplier');
        }

        return view('livewire.lwSuppliers.suppliers-panel', compact('suppliers'));
    }

    // ========================== CRUD ==========================
    public function create()
    {
        $this->resetValidation();
        $this->dispatch('open-modal', 'modal-form-supplier');
    }

    public function update($id)
    {
        $this->isEditing = true;
        $this->supplier = Supplier::find($id);

        $this->resetValidation();
        $this->dispatch('open-modal', 'modal-form-supplier');
    }

    // public function destroyConfirmation($id) {
    // 	$this->idForDeleting = $id;
    // 	$this->dispatch('deleteSupplier', supplierId : $id);
    // }

    public function confirmadoAccionar($data) {}

    public function destroy($id)
    {

        Supplier::findOrFail($id)->update(['is_active' => false]);
        // $this->suppliers = Supplier::all();
    }

    public function save()
    {
        $this->validate();
        $this->supplier->save();

        $this->resetPage(pageName: 'pageSupplier');
        $this->dispatch('close-modal', 'modal-form-supplier');

        // $this->suppliers = Supplier::all();
        $this->supplier = new Supplier;
        $this->isEditing = false;
    }
}
