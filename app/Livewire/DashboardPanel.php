<?php

namespace App\Livewire;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardPanel extends Component
{
	public $totalSales;
	public $totalCustomers;
	public $totalProducts;
	public $recentSales;

	public $ventasPorMes;
	public $ventasTotales;
	public $progresoVentas;

	public $topSellers;
	public $paymentComparison;

	public function mount()
	{
		$this->totalSales = Sale::count();
		$this->totalCustomers = Customer::count();
		$this->totalProducts = Product::count();
		$this->recentSales = Sale::latest()->take(3)->get();

		$this->topSellers = $this->getTopSellers();
		$this->paymentComparison = $this->getPaymentComparison();

		$this->ventasPorMes = Sale::selectRaw('MONTH(sale_date) as mes, SUM(total) as total')
		->groupBy('mes')
		->orderBy('mes')
		->pluck('total', 'mes')
		->toArray();
	}
	
	public function getTopSellers()
	{
		return Sale::select('user_id', DB::raw('count(*) as sales_count'))
			->whereIn('user_id', [1, 2, 3])
			->groupBy('user_id')
			->orderByDesc('sales_count')
			->get()
			->map(function($sale) {
				$sale->user = User::find($sale->user_id);
				return $sale;
			});
	}

	public function getPaymentComparison()
	{
		$totalSales = Sale::count();

		$paypalSales = Sale::where('payment_method_id', 2)->count();
		$cashSales = Sale::where('payment_method_id', 1)->count();

		$paypalPercentage = $totalSales > 0 ? ($paypalSales / $totalSales) * 100 : 0;
		$cashPercentage = $totalSales > 0 ? ($cashSales / $totalSales) * 100 : 0;

		return [
			'paypal' => round($paypalPercentage),
			'cash' => round($cashPercentage),
		];
	}

	public function render()
	{
		return view('livewire.lwDashboard.dashboard-panel');
	}
}
