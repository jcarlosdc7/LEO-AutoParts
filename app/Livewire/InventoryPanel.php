<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class InventoryPanel extends Component
{
	use WithPagination;
	use WithFileUploads;

	public Product $product;
	public Category $category;
	public Supplier $supplier;

	public $categories;
	public $suppliers;

	public $modal = false;
	public $isEditing = false;

	public $viewMode = 'list';

	public $fields = [
		'Nombre',
		'Código',
		'Marca',
	];

	public $newImagePathKey;
	public $newImagePath;

	// ========================== VALIDACION ==========================
	protected $rules = [
		'product.code' => 'required|string|max:255',
		'product.name' => 'required|string|max:255',
		'product.description' => 'required|string|max:255',
		'product.brand' => 'required|string|max:255',
		'product.model' => 'required|string|max:255',
		'product.supplier_id' => 'required',
		'product.category_id' => 'required',
		'product.stock' => 'required',
		'product.min_stock' => 'required',
		'product.price' => 'required',
		'newImagePath' => 'nullable|mimes:jpg,png,jpeg'
	];

	public $searching = '';
	public $searchMode = 'Nombre';

	public function mount() 
	{
		$this->product = new Product();

		$this->categories = Category::all();
		$this->suppliers = Supplier::all();
	}

	public function render()
	{
		switch($this->searchMode) {
			case 'Nombre':
				$products = Product::where('is_active', true)->where('name', 'like', '%' . $this->searching . '%') ->paginate(10, pageName: 'pageProduct');
				break;

			case 'Código':
				$products = Product::where('is_active', true)->where('code', 'like', '%' . $this->searching . '%') ->paginate(10, pageName: 'pageProduct');
				break;

			case 'Marca':
				$products = Product::where('is_active', true)->where('brand', 'like', '%' . $this->searching . '%') ->paginate(10, pageName: 'pageProduct');
				break;
			
			default:
				$products = Product::where('is_active', true)->paginate(10, pageName: 'pageProduct');
				break;
		}

		// EN CASO DE QUE LA ULTIMA PAGINA SE QUEDE SIN REGISTROS PARA RENDERIZAR AQUI REGRESAMOS A LA PAGINA ANTERIOR Y RECARGAMOS
		if($products->count() === 0) {
			$this->previousPage(pageName: 'pageProduct');
			$products = Product::where('is_active', true)->paginate(10, pageName: 'pageProduct');
		}
		
		return view('livewire.lwInventory.inventory-panel', compact('products'));
	}

	// ========================== CRUD ==========================
	public function create() 
	{
		$this->resetValidation();
		$this->dispatch('open-modal', 'modal-form-product');
	}

	public function update($id) 
	{
		$this->isEditing = true;
		$this->product = Product::find($id);
		
		$this->resetValidation();
		$this->dispatch('open-modal', 'modal-form-product');
	}

	public function destroy($id) 
	{
		Product::findOrFail($id)->update(['is_active' => false]);
	}

	public function save()
	{
		if($this->product->category_id == null){
			$this->product->category_id = 1;
		}

		if($this->product->supplier_id == null){
			$this->product->supplier_id = 1;
		}

		if($this->newImagePath) {
			$tempPath = $this->newImagePath->StoreAs('productImages', 'product_' . $this->product->code . '_image.jpg', 'public');
			$this->product->image_path = $tempPath;
		}

		$this->validate();
		$this->product->save();

		$this->dispatch('close-modal', 'modal-form-product');

		$this->product = new Product();
		$this->isEditing = false;

		// AQUI LE INDICO A LIVEWIRE QUE CAMBIE LA LLAVE DE EL INPUT IMAGE YA QUE LIVEWIRE NO HACE UN SEGUIMIENTO CORRECTO DE LOS INPUT DE TIPO FILE 
		// DE NO HACERLO EL INPUT GUARDARA CONSTANTEMENTE LA ULTIMA IMAGEN SUBIDA.
		$this->newImagePathKey = rand();
	}

	// ========================== ADICIONALES ==========================
	public function getSupplierName($id) {
		$this->supplier = Supplier::find($id);
		return $this->supplier->name;
	}

	public function getCategoryName($id) {
		$this->category = Category::find($id);
		return $this->category->name;
	}

	public function setCardMode() {
		$this->viewMode = 'card';
	}

	public function setListMode() {
		$this->viewMode = 'list';
	}

	public function view($id) {
		$this->product = Product::find($id);
		$this->dispatch('open-modal', 'modal-view-product');
	}
}
