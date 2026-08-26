<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\Media\ProductImageService;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class InventoryPage extends Component
{
    use WithFileUploads;
    use WithPagination;

    public Product $product;

    public $categories;

    public $suppliers;

    public bool $isEditing = false;

    public string $viewMode = 'list';

    public array $fields = ['Nombre', 'Código', 'Marca'];

    public $newImagePathKey;

    public $newImagePath;

    public string $searching = '';

    public string $searchMode = 'Nombre';

    protected $rules = [
        'product.code' => ['required', 'string', 'max:255'],
        'product.name' => ['required', 'string', 'max:255'],
        'product.description' => ['required', 'string', 'max:255'],
        'product.brand' => ['required', 'string', 'max:255'],
        'product.model' => ['required', 'string', 'max:255'],
        'product.supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
        'product.category_id' => ['required', 'integer', 'exists:categories,id'],
        'product.stock' => ['required', 'integer', 'min:0'],
        'product.min_stock' => ['required', 'integer', 'min:0'],
        'product.cost_price' => ['required', 'numeric', 'min:0'],
        'product.price' => ['required', 'numeric', 'min:0'],
        'newImagePath' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
    ];

    public function mount(): void
    {
        $this->product = new Product;
        $this->categories = Category::query()->orderBy('name')->get();
        $this->suppliers = Supplier::query()->orderBy('name')->get();
    }

    public function render(): View
    {
        $searchColumn = match ($this->searchMode) {
            'Código' => 'code',
            'Marca' => 'brand',
            default => 'name',
        };

        $products = Product::query()
            ->with(['category', 'supplier'])
            ->when(trim($this->searching) !== '', function ($query) use ($searchColumn) {
                $query->where($searchColumn, 'like', '%'.trim($this->searching).'%');
            })
            ->orderBy('name')
            ->paginate(10, pageName: 'pageProduct');

        return view('livewire.inventory.index', compact('products'));
    }

    public function updatedSearching(): void
    {
        $this->resetPage('pageProduct');
    }

    public function updatedSearchMode(): void
    {
        $this->resetPage('pageProduct');
    }

    public function create(): void
    {
        $this->product = new Product;
        $this->isEditing = false;
        $this->reset('newImagePath');
        $this->resetValidation();
        $this->dispatch('open-modal', 'modal-form-product');
    }

    public function update(int $id): void
    {
        $this->product = Product::findOrFail($id);
        $this->isEditing = true;
        $this->reset('newImagePath');
        $this->resetValidation();
        $this->dispatch('open-modal', 'modal-form-product');
    }

    public function destroy(int $id): void
    {
        Product::findOrFail($id)->delete();
        $this->dispatch('productDeleted');
    }

    public function save(ProductImageService $images): void
    {
        $this->validate();

        if ($this->newImagePath) {
            $extension = strtolower($this->newImagePath->getClientOriginalExtension());
            $path = $this->newImagePath->storeAs(
                'productImages',
                'product_'.$this->product->code.'_image.'.$extension,
                'public',
            );

            $this->product->image_path = $path;
            $images->generate($path);
        }

        $this->product->save();
        $this->dispatch('close-modal', 'modal-form-product');
        $this->dispatch('productSaved');

        $this->product = new Product;
        $this->isEditing = false;
        $this->reset('newImagePath');
        $this->newImagePathKey = random_int(1, PHP_INT_MAX);
    }

    public function setCardMode(): void
    {
        $this->viewMode = 'card';
    }

    public function setListMode(): void
    {
        $this->viewMode = 'list';
    }

    public function view(int $id): void
    {
        $this->product = Product::findOrFail($id);
        $this->dispatch('open-modal', 'modal-view-product');
    }
}
