<?php

namespace App\Livewire;

use App\Models\PaymentMethod;
use App\Models\Refund;
use App\Models\Sale;
use App\Models\SaleReturnItem;
use App\Services\ReturnService;
use App\Services\SaleService;
use App\Support\Decimal;
use App\Support\Money;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class SalesHistoryPanel extends Component
{
    use WithPagination;

    public $selectedSale;

    public $saleDetails = [];

    public ?int $saleToVoidId = null;

    public string $voidReason = '';

    public ?int $returnSaleId = null;

    public string $returnReason = '';

    public array $returnItems = [];

    public $refundMethodId;

    public string $refundReference = '';

    public string $returnOperationId = '';

    public $refundMethods;

    public string $search = '';

    public string $statusFilter = 'all';

    protected $listeners = ['showDetails'];

    public $sortColumn = 'sale_date'; // Columna predeterminada para ordenar

    public $sortDirection = 'desc';  // Dirección predeterminada (descendente)

    public function sortBy(string $column): void
    {
        $allowedColumns = ['id', 'customer_id', 'user_id', 'total', 'sale_date', 'status'];
        abort_unless(in_array($column, $allowedColumns, true), 422, 'Columna de ordenamiento inválida.');

        if ($this->sortColumn === $column) {
            // Si se hace clic en la misma columna, alternar la dirección
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // Si se selecciona otra columna, usar dirección ascendente por defecto
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage(pageName: 'pageSales');
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage(pageName: 'pageSales');
    }

    public function view(int $id): void
    {
        $this->selectedSale = Sale::with([
            'customer', 'user', 'paymentMethod', 'saleDetails.product',
            'saleReturns.items.product', 'saleReturns.refund.paymentMethod', 'saleReturns.creditNote',
        ])->findOrFail($id);
        $this->saleDetails = $this->selectedSale->saleDetails;
        $this->dispatch('open-modal', 'modal-sale-detail');
    }

    public function requestVoid(int $id): void
    {
        $this->ensureAdministrator();

        $sale = Sale::query()->findOrFail($id);
        abort_unless($sale->status === 'completed', 422, 'La venta ya no puede anularse.');

        $this->resetValidation();
        $this->saleToVoidId = $sale->id;
        $this->voidReason = '';
        $this->dispatch('open-modal', 'modal-void-sale');
    }

    public function voidSale(SaleService $saleService): void
    {
        $this->ensureAdministrator();

        $data = $this->validate([
            'saleToVoidId' => ['required', 'integer', Rule::exists('sales', 'id')],
            'voidReason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $saleService->void($data['saleToVoidId'], $data['voidReason'], Auth::user());

        $this->reset(['saleToVoidId', 'voidReason']);
        $this->dispatch('close-modal', 'modal-void-sale');
        $this->dispatch('sale-voided');
    }

    public function requestReturn(int $id): void
    {
        $this->ensureAdministrator();
        $sale = Sale::with('saleDetails.product')->findOrFail($id);
        abort_unless($sale->status === 'completed', 422, 'La venta no admite devoluciones.');

        $returned = SaleReturnItem::query()
            ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
            ->where('sale_returns.sale_id', $sale->id)->where('sale_returns.status', 'completed')
            ->groupBy('sale_return_items.sale_detail_id')
            ->selectRaw('sale_return_items.sale_detail_id, SUM(sale_return_items.quantity) quantity')
            ->pluck('quantity', 'sale_detail_id');

        $this->returnItems = $sale->saleDetails->map(fn ($detail) => [
            'sale_detail_id' => $detail->id,
            'product' => $detail->product->name,
            'original' => $detail->quantity,
            'returned' => (int) ($returned[$detail->id] ?? 0),
            'available' => $detail->quantity - (int) ($returned[$detail->id] ?? 0),
            'unit_price' => (string) $detail->price,
            'quantity' => 0,
            'restock' => true,
            'condition' => 'sellable',
        ])->all();
        abort_if(collect($this->returnItems)->sum('available') === 0, 422, 'La venta ya fue devuelta completamente.');

        $this->refundMethods = PaymentMethod::where('is_active', true)->orderBy('name')->get();
        $this->refundMethodId = $this->refundMethods->firstWhere('code', 'CASH')?->id ?? $this->refundMethods->first()?->id;
        $this->returnSaleId = $sale->id;
        $this->returnReason = '';
        $this->refundReference = '';
        $this->returnOperationId = (string) Str::uuid();
        $this->resetValidation();
        $this->dispatch('open-modal', 'modal-return-sale');
    }

    public function processReturn(ReturnService $returnService): void
    {
        $this->ensureAdministrator();
        $data = $this->validate([
            'returnSaleId' => ['required', 'exists:sales,id'],
            'returnReason' => ['required', 'string', 'min:10', 'max:1000'],
            'refundMethodId' => ['required', 'exists:payment_methods,id'],
            'refundReference' => ['nullable', 'string', 'max:255'],
            'returnItems.*.quantity' => ['required', 'integer', 'min:0'],
            'returnItems.*.restock' => ['required', 'boolean'],
            'returnItems.*.condition' => ['required', 'in:sellable,damaged,defective,quarantine'],
        ]);
        $items = collect($data['returnItems'])->filter(fn ($item) => $item['quantity'] > 0)->values()->all();
        if ($items === []) {
            $this->addError('returnItems', 'Indique al menos una cantidad a devolver.');

            return;
        }

        $returnService->process($data['returnSaleId'], $items, $data['refundMethodId'], $data['returnReason'], Auth::user(), $this->returnOperationId, $data['refundReference'] ?: null);
        $this->reset(['returnSaleId', 'returnReason', 'returnItems', 'refundMethodId', 'refundReference', 'returnOperationId']);
        $this->dispatch('close-modal', 'modal-return-sale');
        $this->dispatch('return-completed');
    }

    public function render()
    {
        $query = Sale::with(['customer', 'user', 'paymentMethod', 'saleDetails', 'saleReturns.items'])
            ->when($this->search !== '', function ($query): void {
                $term = trim($this->search);
                $query->where(function ($query) use ($term): void {
                    $query->where('id', $term)
                        ->orWhereHas('customer', fn ($customerQuery) => $customerQuery
                            ->where('name', 'like', '%'.$term.'%')
                            ->orWhere('dni_ruc', 'like', '%'.$term.'%'));
                });
            })
            ->when($this->statusFilter === 'completed', fn ($query) => $query->where('status', 'completed')->whereDoesntHave('saleReturns', fn ($returns) => $returns->where('status', 'completed')))
            ->when($this->statusFilter === 'returned', fn ($query) => $query->whereHas('saleReturns', fn ($returns) => $returns->where('status', 'completed')))
            ->when($this->statusFilter === 'voided', fn ($query) => $query->where('status', 'voided'));

        $sales = $query->orderBy($this->sortColumn, $this->sortDirection)->paginate(12, pageName: 'pageSales');
        $grossSales = Decimal::round((string) Sale::where('status', 'completed')->sum('total'));
        $refunded = Decimal::round((string) Refund::where('status', 'completed')->sum('amount'));

        return view('livewire.lwSalesHistory.sales-history-panel', [
            'sales' => $sales,
            'grossSales' => $grossSales,
            'refunded' => $refunded,
            'netSales' => Decimal::subtract($grossSales, $refunded),
            'voidedCount' => Sale::where('status', 'voided')->count(),
        ]);
    }

    public function returnLineTotal(array $item): string
    {
        $quantity = filter_var($item['quantity'] ?? 0, FILTER_VALIDATE_INT);

        return $quantity && $quantity > 0
            ? Money::fromUnitPrice((string) ($item['unit_price'] ?? '0.0000'), $quantity)->amount()
            : '0.00';
    }

    public function returnRefundTotal(): string
    {
        return collect($this->returnItems)->reduce(
            fn (string $total, array $item): string => Decimal::add($total, $this->returnLineTotal($item)),
            '0.00',
        );
    }

    private function ensureAdministrator(): void
    {
        abort_unless(Auth::user()?->is_active && Auth::user()?->hasRole('Administrador'), 403);
    }
}
