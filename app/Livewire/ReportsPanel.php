<?php

namespace App\Livewire;

use App\Exports\CustomersExport;
use App\Exports\PaymentsExport;
use App\Exports\ProductsExport;
use App\Exports\SalesExport;
use App\Exports\StockExport;
use App\Exports\UsersExport;
use App\Models\Category;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelWriter;

class ReportsPanel extends Component
{
	public $categories;

	public $selectedCategory;
	
	public function mount() 
	{
		$this->categories = Category::all();
	}

	public function render()
	{
		return view('livewire.lwReports.reports-panel');
	}

	// REPORTE DE VENTAS
	public function reportSalesEXCEL() {
		return Excel::download(new SalesExport, 'sales.xlsx');
	}

	public function reportSalesPDF() {
		return Excel::download(new SalesExport, 'sales.pdf', ExcelWriter::DOMPDF);
	}

	// REPORTE DE PRODUCTOS
	public function reportProductsEXCEL() {
		if($this->selectedCategory === null) {
			$this->selectedCategory = 0;
		}
		return Excel::download(new ProductsExport($this->selectedCategory), 'products.xlsx');

		$this->dispatch('close-modal', 'modal-form-paramProductsEXCEL');
	}

	public function reportProductsPDF() {
		if($this->selectedCategory === null) {
			$this->selectedCategory = 0;
		}
		return Excel::download(new ProductsExport($this->selectedCategory), 'products.pdf', ExcelWriter::DOMPDF);
		
		$this->dispatch('close-modal', 'modal-form-paramProductsPDF');
	}

	// REPORTE DE CLIENTES
	public function reportCustomersEXCEL() {
		return Excel::download(new CustomersExport, 'customers.xlsx');
	}

	public function reportCustomersPDF() {
		return Excel::download(new CustomersExport, 'customers.pdf', ExcelWriter::DOMPDF);
	}

	// REPORTE DE USUARIOS
	public function reportUsersEXCEL() {
		return Excel::download(new UsersExport, 'users.xlsx');
	}

	public function reportUsersPDF() {
		return Excel::download(new UsersExport, 'users.pdf', ExcelWriter::DOMPDF);
	}

	// REPORTE DE PAGOS
	public function reportPaymentsEXCEL() {
		return Excel::download(new PaymentsExport, 'payments.xlsx');
	}

	public function reportPaymentsPDF() {
		return Excel::download(new PaymentsExport, 'payments.pdf', ExcelWriter::DOMPDF);
	}

	// REPORTE DE STOCK
	public function reportStockEXCEL() {
		return Excel::download(new StockExport, 'stock.xlsx');
	}

	public function reportStockPDF() {
		return Excel::download(new StockExport, 'stock.pdf', ExcelWriter::DOMPDF);
	}
}
