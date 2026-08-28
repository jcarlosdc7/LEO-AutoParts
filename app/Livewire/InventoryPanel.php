<?php

namespace App\Livewire;

use App\Exports\KardexExport;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\AuditService;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class InventoryPanel extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected $rules = [
        'product.code' => 'required|string|max:64',
        'product.name' => 'required|string|max:255',
        'product.description' => 'nullable|string|max:2000',
        'product.brand' => 'required|string|max:255',
        'product.model' => 'required|string|max:255',
        'product.supplier_id' => 'required|integer',
        'product.category_id' => 'required|integer',
        'product.min_stock' => 'required|integer|min:0|max:1000000',
        'product.price' => 'required|decimal:0,2|min:0|max:99999999.99',
    ];

    public Product $product;

    public $categories;

    public $suppliers;

    public bool $isEditing = false;

    public $newImagePath;

    public string $newImagePathKey = '';

    public string $searching = '';

    public string $statusFilter = 'active';

    public string $stockFilter = 'all';

    public string $categoryFilter = '';

    public ?int $adjustmentProductId = null;

    public string $adjustmentMode = 'increase';

    public int|string $adjustmentValue = 1;

    public string $adjustmentReason = '';

    public string $adjustmentOperationId = '';

    public ?int $kardexProductId = null;

    public string $kardexFrom = '';

    public string $kardexTo = '';

    public function mount(): void
    {
        $this->ensureViewer();
        $this->product = new Product;
        $this->categories = Category::orderBy('name')->get();
        $this->suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $this->newImagePathKey = (string) Str::uuid();
    }

    public function updatedSearching(): void
    {
        $this->resetPage(pageName: 'pageProduct');
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage(pageName: 'pageProduct');
    }

    public function updatedStockFilter(): void
    {
        $this->resetPage(pageName: 'pageProduct');
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage(pageName: 'pageProduct');
    }

    public function render()
    {
        $query = Product::query()->with(['supplier:id,name', 'category:id,name']);
        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }
        if ($this->stockFilter === 'out') {
            $query->where('stock', 0);
        } elseif ($this->stockFilter === 'low') {
            $query->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock');
        }
        if ($this->categoryFilter !== '') {
            $query->where('category_id', (int) $this->categoryFilter);
        }
        if ($this->searching !== '') {
            $term = '%'.trim($this->searching).'%';
            $query->where(function ($query) use ($term): void {
                $query->where('code', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('brand', 'like', $term)
                    ->orWhereHas('category', fn ($category) => $category->where('name', 'like', $term));
            });
        }
        $products = $query->orderBy('name')->paginate(25, pageName: 'pageProduct');

        $kardexProduct = null;
        $kardexMovements = null;
        $kardexOpening = 0;
        if ($this->kardexProductId) {
            $kardexProduct = Product::query()->findOrFail($this->kardexProductId);
            $movements = StockMovement::query()
                ->with(['warehouse:id,code,name', 'actor:id,name'])
                ->where('product_id', $kardexProduct->id)
                ->orderBy('occurred_at')
                ->orderBy('id');
            if ($this->kardexFrom !== '') {
                $from = Carbon::parse($this->kardexFrom)->startOfDay();
                $kardexOpening = (int) StockMovement::query()
                    ->where('product_id', $kardexProduct->id)
                    ->where('occurred_at', '<', $from)
                    ->sum('quantity');
                $movements->where('occurred_at', '>=', $from);
            }
            if ($this->kardexTo !== '') {
                $movements->where('occurred_at', '<=', Carbon::parse($this->kardexTo)->endOfDay());
            }
            $kardexMovements = $movements->paginate(25, pageName: 'kardexPage');
        }

        return view('livewire.lwInventory.inventory-panel', compact(
            'products',
            'kardexProduct',
            'kardexMovements',
            'kardexOpening',
        ));
    }

    public function create(): void
    {
        $this->ensureAdministrator();
        abort_if($this->categories->isEmpty() || $this->suppliers->isEmpty(), 422, 'Debe existir al menos una categoría y un proveedor activo.');
        $this->product = new Product([
            'category_id' => $this->categories->first()->id,
            'supplier_id' => $this->suppliers->first()->id,
            'min_stock' => 0,
            'is_active' => true,
        ]);
        $this->isEditing = false;
        $this->newImagePath = null;
        $this->resetValidation();
        $this->dispatch('open-modal', 'modal-form-product');
    }

    public function editProduct(int $id): void
    {
        $this->ensureAdministrator();
        $this->product = Product::query()->findOrFail($id);
        $this->isEditing = true;
        $this->newImagePath = null;
        $this->resetValidation();
        $this->dispatch('open-modal', 'modal-form-product');
    }

    public function save(): void
    {
        $this->ensureAdministrator();
        $validated = $this->validate([
            'product.code' => ['required', 'string', 'max:64', Rule::unique('products', 'code')->ignore($this->product->id)],
            'product.name' => ['required', 'string', 'max:255'],
            'product.description' => ['nullable', 'string', 'max:2000'],
            'product.brand' => ['required', 'string', 'max:255'],
            'product.model' => ['required', 'string', 'max:255'],
            'product.supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')->where('is_active', true)],
            'product.category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'product.min_stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'product.price' => ['required', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'newImagePath' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
        ]);

        $this->product->fill($validated['product']);
        if ($this->newImagePath) {
            $this->product->image_path = $this->newImagePath->storeAs(
                'productImages',
                'product_'.$this->product->code.'_image.jpg',
                'public',
            );
        }
        $this->product->save();

        $this->dispatch('close-modal', 'modal-form-product');
        $this->product = new Product;
        $this->isEditing = false;
        $this->newImagePath = null;
        $this->newImagePathKey = (string) Str::uuid();
    }

    public function archive(int $id): void
    {
        $this->ensureAdministrator();
        $product = Product::query()->findOrFail($id);
        $before = $product->getAttributes();
        $product->update(['is_active' => false]);
        AuditService::record('inventory.product_archived', $product, $before, $product->getAttributes(), Auth::id());
    }

    public function reactivate(int $id): void
    {
        $this->ensureAdministrator();
        $product = Product::query()->findOrFail($id);
        $before = $product->getAttributes();
        $product->update(['is_active' => true]);
        AuditService::record('inventory.product_reactivated', $product, $before, $product->getAttributes(), Auth::id());
    }

    public function requestAdjustment(int $id): void
    {
        $this->ensureAdministrator();
        $product = Product::query()->where('is_active', true)->findOrFail($id);
        $this->adjustmentProductId = $product->id;
        $this->adjustmentMode = 'increase';
        $this->adjustmentValue = 1;
        $this->adjustmentReason = '';
        $this->adjustmentOperationId = (string) Str::uuid();
        $this->resetValidation();
        $this->dispatch('open-modal', 'modal-adjust-inventory');
    }

    public function processAdjustment(InventoryService $inventory): void
    {
        $this->ensureAdministrator();
        $data = $this->validate([
            'adjustmentProductId' => ['required', 'integer', Rule::exists('products', 'id')->where('is_active', true)],
            'adjustmentMode' => ['required', Rule::in(['increase', 'decrease', 'count'])],
            'adjustmentValue' => ['required', 'integer', 'min:0', 'max:1000000'],
            'adjustmentReason' => ['required', 'string', 'min:10', 'max:1000'],
            'adjustmentOperationId' => ['required', 'uuid'],
        ]);

        $inventory->adjust(
            $data['adjustmentProductId'],
            $data['adjustmentMode'],
            (int) $data['adjustmentValue'],
            $data['adjustmentReason'],
            Auth::user(),
            $data['adjustmentOperationId'],
        );
        $this->reset(['adjustmentProductId', 'adjustmentReason', 'adjustmentOperationId']);
        $this->dispatch('close-modal', 'modal-adjust-inventory');
    }

    public function showKardex(int $id): void
    {
        $this->ensureViewer();
        $this->kardexProductId = Product::query()->findOrFail($id)->id;
        $this->kardexFrom = '';
        $this->kardexTo = '';
        $this->resetPage(pageName: 'kardexPage');
        $this->dispatch('open-modal', 'modal-kardex');
    }

    public function applyKardexDates(): void
    {
        $this->validate([
            'kardexFrom' => ['nullable', 'date'],
            'kardexTo' => ['nullable', 'date', 'after_or_equal:kardexFrom'],
        ]);
        $this->resetPage(pageName: 'kardexPage');
    }

    public function exportKardex()
    {
        $this->ensureViewer();
        $data = $this->validate([
            'kardexProductId' => ['required', 'integer', Rule::exists('products', 'id')],
            'kardexFrom' => ['required', 'date'],
            'kardexTo' => ['required', 'date', 'after_or_equal:kardexFrom', 'before_or_equal:'.Carbon::parse($this->kardexFrom)->addYear()->format('Y-m-d')],
        ]);
        $product = Product::query()->findOrFail($data['kardexProductId']);

        return Excel::download(
            new KardexExport($product->id, $data['kardexFrom'], $data['kardexTo']),
            'kardex-'.$product->code.'-'.$data['kardexFrom'].'-'.$data['kardexTo'].'.xlsx',
        );
    }

    public function adjustmentPreview(): int
    {
        $stock = $this->adjustmentProductId
            ? (int) Product::query()->whereKey($this->adjustmentProductId)->value('stock')
            : 0;
        $value = filter_var($this->adjustmentValue, FILTER_VALIDATE_INT);
        if ($value === false) {
            return $stock;
        }

        return match ($this->adjustmentMode) {
            'increase' => $stock + $value,
            'decrease' => $stock - $value,
            'count' => $value,
            default => $stock,
        };
    }

    private function ensureViewer(): void
    {
        abort_unless(Auth::user()?->is_active && Auth::user()?->hasAnyRole(['Administrador', 'Contador']), 403);
    }

    private function ensureAdministrator(): void
    {
        abort_unless(Auth::user()?->is_active && Auth::user()?->hasRole('Administrador'), 403);
    }
}
