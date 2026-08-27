<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\SaleService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

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

    public $paymentMethod;

    public $paymentMethods;

    public bool $paymentAffectsCash = false;

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
        $this->customer = new Customer;
        $this->customer_types = CustomerType::all();
        $this->customers = Customer::where('is_active', true)->get();
        $this->paymentMethods = PaymentMethod::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $defaultPaymentMethod = $this->paymentMethods->firstWhere('code', 'CASH') ?? $this->paymentMethods->first();
        $this->paymentMethod = $defaultPaymentMethod?->id;
        $this->paymentAffectsCash = (bool) $defaultPaymentMethod?->affects_cash_drawer;

        // PRODUCTOS
        $this->product = new Product;

        $this->categories = Category::all();
        $this->suppliers = Supplier::all();
    }

    public function render()
    {
        // PRODUCTOS
        switch ($this->searchMode) {
            case 'Nombre':
                $products = Product::where('is_active', true)->where('name', 'like', '%'.$this->searching.'%')->get();
                break;

            case 'Código':
                $products = Product::where('is_active', true)->where('code', 'like', '%'.$this->searching.'%')->get();
                break;

            case 'Marca':
                $products = Product::where('is_active', true)->where('brand', 'like', '%'.$this->searching.'%')->get();
                break;

            default:
                $products = Product::where('is_active', true)->get();
                break;
        }

        return view('livewire.lwInvoicing.invoicing-panel', compact('products'));
    }

    // ========================== ADICIONALES ==========================
    public function getSupplierName($id)
    {
        $this->supplier = Supplier::find($id);

        return $this->supplier->name;
    }

    public function getCategoryName($id)
    {
        $this->category = Category::find($id);

        return $this->category->name;
    }

    public function rowSelect($id)
    {
        $this->selectedProduct = Product::find($id);
        $this->productCount = 1;
    }

    // AÑADIR PRODUCTO A LA FACTURA
    public function addToInvoice($id)
    {
        $this->validate(['productCount' => 'required|integer|min:1|max:999']);
        $newProduct = Product::where('is_active', true)->find($id);

        if (! $newProduct) {
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
            $this->invoiceTable[$index]['subtotal'] = $this->invoiceTable[$index]['price'] * $this->invoiceTable[$index]['quantity'];
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

        $this->customer = new Customer;
    }

    public function saveInvoice(SaleService $saleService)
    {
        $this->validate([
            'cId' => ['required', 'exists:customers,id'],
            'paymentMethod' => ['required', 'exists:payment_methods,id'],
            'amount' => [$this->paymentAffectsCash ? 'required' : 'nullable', 'numeric', 'min:0'],
        ]);

        $sale = $saleService->create(
            $this->invoiceTable,
            (int) $this->cId,
            (int) $this->paymentMethod,
            $this->amount !== null ? (float) $this->amount : null,
            Auth::user(),
        );

        $options = new Options;
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false);
        $options->set('isRemoteEnabled', false);
        $options->setChroot(public_path());
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('exports.invoice', ['sale' => $sale])->render());
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        Storage::disk('local')->put("invoices/invoice-{$sale->id}.pdf", $dompdf->output());
        $this->dispatch('downloadInvoice', route('invoices.download', $sale));
        $this->clearInvoice();
    }

    public function updatedAmount()
    {
        if ($this->totalFinal < $this->amount && $this->amount != '') {
            $this->change = $this->amount - $this->totalFinal;
        } else {
            $this->change = 0;
        }
    }

    public function updatedPaymentMethod(): void
    {
        $method = $this->paymentMethods->firstWhere('id', (int) $this->paymentMethod);
        $this->paymentAffectsCash = (bool) $method?->affects_cash_drawer;

        if (! $this->paymentAffectsCash) {
            $this->amount = null;
            $this->change = 0;
        }
    }
}
