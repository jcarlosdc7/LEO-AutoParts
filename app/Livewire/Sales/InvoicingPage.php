<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Services\Sales\InvoicePdfService;
use App\Services\Sales\SaleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class InvoicingPage extends Component
{
    public $cId;

    public $cDniRuc;

    public $cName;

    public $cEmail;

    public $cPhone;

    public $cAddress;

    public $cCity;

    public $cType;

    public Customer $customer;

    public $customers;

    public $paymentMethod = 1;

    public array $cities = [
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

    public array $fields = ['Nombre', 'Código', 'Marca'];

    public string $searching = '';

    public string $searchMode = 'Nombre';

    public $selectedProduct = null;

    public int $productCount = 1;

    public array $invoiceTable = [];

    public float $totalFinal = 0;

    public float $change = 0;

    public $amount = null;

    public function mount(): void
    {
        $this->customer = new Customer;
        $this->customers = Customer::query()
            ->with('customerType')
            ->orderBy('name')
            ->get();
    }

    public function render(): View
    {
        $searchColumn = match ($this->searchMode) {
            'Código' => 'code',
            'Marca' => 'brand',
            default => 'name',
        };

        $products = Product::query()
            ->with(['supplier', 'category'])
            ->when(trim($this->searching) !== '', function ($query) use ($searchColumn) {
                $query->where($searchColumn, 'like', '%'.trim($this->searching).'%');
            })
            ->orderBy('name')
            ->get();

        $paymentMethods = PaymentMethod::query()->orderBy('name')->get();

        return view('livewire.sales.invoicing', compact('products', 'paymentMethods'));
    }

    public function rowSelect(int $id): void
    {
        $this->selectedProduct = Product::findOrFail($id);
        $this->productCount = 1;
    }

    public function addToInvoice(int $id): void
    {
        $product = Product::findOrFail($id);
        $quantity = max(1, $this->productCount);
        $index = collect($this->invoiceTable)->search(fn (array $item): bool => $item['id'] === $product->id);
        $currentQuantity = $index === false ? 0 : $this->invoiceTable[$index]['quantity'];

        if (($product->stock - $currentQuantity) < $quantity) {
            $this->dispatch('noStock');

            return;
        }

        if ($index !== false) {
            $this->invoiceTable[$index]['quantity'] += $quantity;
            $this->invoiceTable[$index]['subtotal'] = $this->invoiceTable[$index]['price'] * $this->invoiceTable[$index]['quantity'];
        } else {
            $this->invoiceTable[] = [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'image_path' => $product->image_path,
                'price' => $product->price,
                'quantity' => $quantity,
                'subtotal' => $product->price * $quantity,
            ];
        }

        $this->recalculateTotals();
        $this->productCount = 1;
    }

    public function removeFromInvoice(int $id): void
    {
        $this->invoiceTable = array_values(array_filter(
            $this->invoiceTable,
            fn (array $item): bool => $item['id'] !== $id,
        ));

        $this->recalculateTotals();
    }

    public function clearInvoice(): void
    {
        $this->invoiceTable = [];
        $this->totalFinal = 0;
        $this->change = 0;
        $this->amount = null;
        $this->reset(['cId', 'cDniRuc', 'cName', 'cEmail', 'cPhone', 'cAddress', 'cCity', 'cType']);
    }

    public function openCustomerSelection(): void
    {
        $this->dispatch('open-modal', 'modal-list-customers');
    }

    public function selectCustomer(int $id): void
    {
        $selectedCustomer = Customer::query()->with('customerType')->findOrFail($id);

        $this->cId = $selectedCustomer->id;
        $this->cDniRuc = $selectedCustomer->dni_ruc;
        $this->cName = $selectedCustomer->name;
        $this->cEmail = $selectedCustomer->email;
        $this->cPhone = $selectedCustomer->phone;
        $this->cAddress = $selectedCustomer->address;
        $this->cCity = $selectedCustomer->city;
        $this->cType = $selectedCustomer->customerType?->name;
        $this->customer = new Customer;

        $this->dispatch('close-modal', 'modal-list-customers');
    }

    public function saveInvoice(SaleService $sales, InvoicePdfService $invoices): void
    {
        try {
            $sale = $sales->create(
                Auth::user(),
                (int) $this->cId,
                (int) $this->paymentMethod,
                $this->invoiceTable,
                $this->amount,
            );
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());
            $this->dispatch('saleError', $exception->getMessage());

            return;
        }

        $this->dispatch('downloadInvoice', $invoices->generate($sale));
        $this->clearInvoice();
    }

    public function updatedAmount(): void
    {
        $this->recalculateChange();
    }

    private function recalculateTotals(): void
    {
        $this->totalFinal = (float) collect($this->invoiceTable)->sum('subtotal');
        $this->recalculateChange();
    }

    private function recalculateChange(): void
    {
        $amount = is_numeric($this->amount) ? (float) $this->amount : 0;
        $this->change = max(0, $amount - $this->totalFinal);
    }
}
