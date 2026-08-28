<?php

namespace App\Livewire;

use App\Models\CashSession;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardPanel extends Component
{
    public int $totalSales = 0;

    public int $totalCustomers = 0;

    public int $totalProducts = 0;

    public int $lowStockCount = 0;

    public int $openCashSessions = 0;

    public float $grossSales = 0;

    public float $refundedTotal = 0;

    public float $netSales = 0;

    public float $todayNetSales = 0;

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
        $this->grossSales = (float) Sale::where('status', 'completed')->sum('total');
        $this->refundedTotal = (float) Refund::where('status', 'completed')->sum('amount');
        $this->netSales = $this->grossSales - $this->refundedTotal;

        $todayGross = (float) Sale::where('status', 'completed')->whereDate('sale_date', today())->sum('total');
        $todayRefunded = (float) Refund::where('status', 'completed')->whereDate('processed_at', today())->sum('amount');
        $this->todayNetSales = $todayGross - $todayRefunded;

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
            ->map(fn ($value): float => (float) $value)
            ->all();

        $this->topSellers = Sale::query()
            ->with('user')
            ->where('status', 'completed')
            ->select('user_id', DB::raw('COUNT(*) as sales_count'), DB::raw('SUM(total) as sales_total'))
            ->groupBy('user_id')
            ->orderByDesc('sales_total')
            ->limit(5)
            ->get();

        $this->paymentBreakdown = Sale::query()
            ->with('paymentMethod')
            ->where('status', 'completed')
            ->select('payment_method_id', DB::raw('COUNT(*) as operation_count'), DB::raw('SUM(total) as operation_total'))
            ->groupBy('payment_method_id')
            ->orderByDesc('operation_total')
            ->get();
    }

    public function render()
    {
        return view('livewire.lwDashboard.dashboard-panel');
    }
}
