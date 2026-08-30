<?php

namespace App\Livewire;

use App\Models\CashSession;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Sale;
use App\Support\Decimal;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardPanel extends Component
{
    public int $totalSales = 0;

    public int $totalCustomers = 0;

    public int $totalProducts = 0;

    public int $lowStockCount = 0;

    public int $openCashSessions = 0;

    public string $grossSales = '0.00';

    public string $refundedTotal = '0.00';

    public string $netSales = '0.00';

    public string $todayNetSales = '0.00';

    public string $maxMonthly = '1.00';

    public string $paymentTotal = '1.00';

    public string $maxSellerTotal = '1.00';

    public $recentSales;

    public array $ventasPorMes = [];

    public $topSellers;

    public $paymentBreakdown;

    public function mount(): void
    {
        $this->totalSales = Sale::where('status', 'completed')->count();
        $this->totalCustomers = Customer::where('is_active', true)->count();
        $this->totalProducts = Product::where('is_active', true)->count();
        $this->lowStockCount = Product::where('is_active', true)->whereColumn('stock', '<=', 'min_stock')->count();
        $this->openCashSessions = CashSession::where('status', 'open')->count();
        $this->grossSales = Decimal::round((string) Sale::where('status', 'completed')->sum('total'));
        $this->refundedTotal = Decimal::round((string) Refund::where('status', 'completed')->sum('amount'));
        $this->netSales = Decimal::subtract($this->grossSales, $this->refundedTotal);

        $todayGross = Decimal::round((string) Sale::where('status', 'completed')->whereDate('sale_date', today())->sum('total'));
        $todayRefunded = Decimal::round((string) Refund::where('status', 'completed')->whereDate('processed_at', today())->sum('amount'));
        $this->todayNetSales = Decimal::subtract($todayGross, $todayRefunded);

        $this->recentSales = Sale::with(['customer', 'user', 'paymentMethod', 'saleDetails', 'saleReturns.items'])
            ->latest('sale_date')
            ->limit(6)
            ->get();

        $this->ventasPorMes = Sale::query()
            ->where('status', 'completed')
            ->whereYear('sale_date', now()->year)
            ->selectRaw('MONTH(sale_date) as month_number, SUM(total) as total')
            ->groupBy('month_number')
            ->orderBy('month_number')
            ->pluck('total', 'month_number')
            ->map(fn ($value): string => Decimal::round((string) $value))
            ->all();
        $this->maxMonthly = Decimal::maximum($this->ventasPorMes);
        if (Decimal::compare($this->maxMonthly, '0') === 0) {
            $this->maxMonthly = '1.00';
        }

        $this->topSellers = Sale::query()
            ->with('user')
            ->where('status', 'completed')
            ->select('user_id', DB::raw('COUNT(*) as sales_count'), DB::raw('SUM(total) as sales_total'))
            ->groupBy('user_id')
            ->orderByDesc('sales_total')
            ->limit(5)
            ->get();
        $this->maxSellerTotal = Decimal::maximum($this->topSellers->pluck('sales_total'));
        if (Decimal::compare($this->maxSellerTotal, '0') === 0) {
            $this->maxSellerTotal = '1.00';
        }

        $this->paymentBreakdown = Sale::query()
            ->with('paymentMethod')
            ->where('status', 'completed')
            ->select('payment_method_id', DB::raw('COUNT(*) as operation_count'), DB::raw('SUM(total) as operation_total'))
            ->groupBy('payment_method_id')
            ->orderByDesc('operation_total')
            ->get();
        $this->paymentTotal = $this->paymentBreakdown->reduce(
            fn (string $total, $row): string => Decimal::add($total, (string) $row->operation_total),
            Decimal::zero(),
        );
        if (Decimal::compare($this->paymentTotal, '0') === 0) {
            $this->paymentTotal = '1.00';
        }
    }

    public function render()
    {
        return view('livewire.lwDashboard.dashboard-panel');
    }
}
