<?php

namespace App\Livewire;

use App\Exports\InvoiceExport;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Supplier;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class InvoicingPanel extends Component
{
	// DATOS DEL CLIENTE FRONTEND
	public $cId;
	public $cDniRuc;
	public $cName;
	public $cEmail;
	public $cPhone;
	public $cAddress;
	public $cCity;
	public $cType;

	// PARA INFORMACION DEL CLIENTE
	public Customer $customer;
	public CustomerType $customerType;
	
	public $customer_types;
	public $customers;

	public $paymentMethod = 1;

	public $cities = [
		"Managua",
		"León",
		"Granada",
		"Masaya",
		"Chinandega",
		"Estelí",
		"Rivas",
		"Bluefields",
		"Jinotega",
		"Matagalpa",
		"Carazo",
		"Boaco",
		"Nueva Segovia",
		"Chontales",
		"Rio San Juan"
	];

	// PARA GESTION DE PRODUCTOS 
	public Product $product;
	public Category $category;
	public Supplier $supplier;

	public $categories;
	public $suppliers;

	public $fields = [
		'Nombre',
		'Código',
		'Marca',
	];

	public $searching = '';
	public $searchMode = 'Nombre';

	public $selectedProduct = null;
	public $productCount = 1;

	// TABLA DE FACTURA (Lista de productos agregados)
	public $invoiceTable = [];

	public $totalFinal = 0;

	// PARA CALCULAR VUELTO
	public $change = 0;
	public $amount = null;

	public function mount()
	{
		// CLIENTES
		$this->customer = new Customer();
		$this->customer_types = CustomerType::all();
		$this->customers = Customer::all();

		// PRODUCTOS
		$this->product = new Product();

		$this->categories = Category::all();
		$this->suppliers = Supplier::all();
	}

	public function render()
	{
		// PRODUCTOS 
		switch($this->searchMode) {
			case 'Nombre':
				$products = Product::where('name', 'like', '%' . $this->searching . '%')->get();
				break;

			case 'Código':
				$products = Product::where('code', 'like', '%' . $this->searching . '%')->get();
				break;

			case 'Marca':
				$products = Product::where('brand', 'like', '%' . $this->searching . '%')->get();
				break;
			
			default:
				$products = Product::all();
				break;
		}

		return view('livewire.lwInvoicing.invoicing-panel', compact('products'));
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

	public function rowSelect($id) {
		$this->selectedProduct = Product::find($id);
		$this->productCount = 1;
	}

	// AÑADIR PRODUCTO A LA FACTURA
	public function addToInvoice($id) {
		$newProduct = Product::find($id);

		if (!$newProduct) {
			return;
		}

		// Verificar si el producto ya está en la tabla de factura
		$inInvoice = collect($this->invoiceTable)->firstWhere('id', $newProduct->id);
		$currentQuantityInInvoice = $inInvoice['quantity'] ?? 0;
	
		// Verificar si el stock disponible es suficiente
		$remainingStock = $newProduct->stock - $currentQuantityInInvoice;
	
		if ($remainingStock <= 0 || $remainingStock < $this->productCount) {
			// Mostrar un mensaje de error si no hay suficiente stock
			$this->dispatch('noStock');
			return;
		}

		// BUSCAR SI EL PRODUCTO YA ESTÁ EN LA TABLA
		$index = collect($this->invoiceTable)->search(fn ($p) => $p['id'] === $newProduct->id);

		if ($index !== false) {
			// SI YA EXISTE, AUMENTAR LA CANTIDAD
			$this->invoiceTable[$index]['quantity'] += $this->productCount;
			$this->invoiceTable[$index]['subtotal'] = $this->invoiceTable[$index]['price'] * $this->productCount;
		} else {
			// SI NO EXISTE, AGREGARLO
			$this->invoiceTable[] = [
				'id' => $newProduct->id,
				'name' => $newProduct->name,
				'code' => $newProduct->code,
				'price' => $newProduct->price,
				'quantity' => $this->productCount,
				'subtotal' => $newProduct->price * $this->productCount,
			];
		}

		$this->totalFinal = collect($this->invoiceTable)->sum('subtotal');
		$this->reset('productCount');
	}

	// REMOVER PRODUCTO DE LA FACTURA
	public function removeFromInvoice($id)
	{
		$this->invoiceTable = array_filter($this->invoiceTable, fn ($p) => $p['id'] !== $id);
		$this->totalFinal = collect($this->invoiceTable)->sum('subtotal');
	}

	// LIMPIAR FACTURA
	public function clearInvoice()
	{
		$this->invoiceTable = [];
		$this->totalFinal = 0;

		$this->reset(['cId', 'cDniRuc', 'cName', 'cEmail', 'cPhone', 'cAddress', 'cCity', 'cType']);
		$this->change = 0;
		$this->amount = null;
	}

	public function getCustomerType($id) 
	{
		$this->customerType = CustomerType::find($id);
		return $this->customerType->name;
	}

	public function openCustomerSelection() 
	{
		$this->dispatch('open-modal', 'modal-list-customers');
	}

	public function selectCustomer($id) 
	{
		$this->dispatch('close-modal', 'modal-list-customers');
		$this->customer = Customer::find($id);

		$this->cId = $this->customer->id;
		$this->cDniRuc = $this->customer->dni_ruc;
		$this->cName = $this->customer->name;
		$this->cEmail = $this->customer->email;
		$this->cPhone = $this->customer->phone;
		$this->cAddress = $this->customer->address;
		$this->cCity = $this->customer->city;
		$this->cType = $this->getCustomerType($this->customer->customer_type_id);

		$this->customer = new Customer();
	}

	public function saveInvoice()
	{
		// Validar que haya productos en la factura
		if (count($this->invoiceTable) == 0 || $this->cId == null) {
			return;
		}
		
		// Obtener informacion del usuario logueado
		$user = Auth::user();

		// Crear la venta
		$sale = Sale::create([
			'customer_id' => $this->cId,
			'user_id' => $user->id,
			'total' => $this->totalFinal,
			'sale_date' => now(), 
			'payment_method_id' => $this->paymentMethod,
		]);

		// Recorrer los productos de la factura y guardarlos en la tabla de detalles
		foreach ($this->invoiceTable as $item) {
			$saleDetail = SaleDetail::create([
				'sale_id' => $sale->id,
				'product_id' => $item['id'],
				'quantity' => $item['quantity'],
				'price' => $item['price'],
				'total' => $item['subtotal'],
			]);

			// Descontar el stock de los productos
			$product = Product::find($item['id']);
			if ($product) {
				$product->stock -= $item['quantity']; // Restar la cantidad vendida del stock
				$product->save();  // Guardar el cambio en el stock
			}
		}
		
		// Generación del PDF con DOMPDF
		$options = new Options();
		$options->set('isHtml5ParserEnabled', true);
		$options->set('isPhpEnabled', true);
		$dompdf = new Dompdf($options);
	
		// Obtener el contenido HTML de la factura (vista Blade)
		$html = view('exports.invoice', ['sale' => $sale])->render();
	
		// Cargar el HTML en DOMPDF
		$dompdf->loadHtml($html);
	
		// Establecer tamaño de papel A4 y orientación vertical
		$dompdf->setPaper('A4', 'portrait');
	
		// Renderizar el PDF
		$dompdf->render();
	
		// Nombre del archivo PDF
		$filename = 'LEO AutoParts - Factura_No_' . $sale->id . '.pdf';
	
		// Guardar el PDF en el almacenamiento público
		Storage::disk('public')->put('facturas/' . $filename, $dompdf->output());
	
		// Emitir evento Livewire con la URL del PDF
		$this->dispatch('downloadInvoice', asset('storage/facturas/' . $filename));
		
		$this->clearInvoice();
	}

	public function updatedAmount() {
		if($this->totalFinal < $this->amount && $this->amount != "") 
			$this->change = $this->amount - $this->totalFinal;
		else
			$this->change = 0;
	}
}
