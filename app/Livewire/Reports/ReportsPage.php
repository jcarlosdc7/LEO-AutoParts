<?php

namespace App\Livewire\Reports;

use App\Exports\Reports\CustomersExport;
use App\Exports\Reports\PaymentsExport;
use App\Exports\Reports\ProductsExport;
use App\Exports\Reports\SalesExport;
use App\Exports\Reports\StockExport;
use App\Exports\Reports\UsersExport;
use App\Models\Category;
use Illuminate\View\View;
use Livewire\Component;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportsPage extends Component
{
    public $categories;

    public $selectedCategory;

    public function mount(): void
    {
        $this->categories = Category::query()->orderBy('name')->get();
    }

    public function render(): View
    {
        return view('livewire.reports.index');
    }

    public function downloadSalesExcel(): BinaryFileResponse
    {
        return Excel::download(new SalesExport, 'sales.xlsx');
    }

    public function downloadSalesPdf(): BinaryFileResponse
    {
        return Excel::download(new SalesExport, 'sales.pdf', ExcelWriter::DOMPDF);
    }

    public function downloadProductsExcel(): BinaryFileResponse
    {
        $categoryId = (int) ($this->selectedCategory ?: 0);
        $this->dispatch('close-modal', 'modal-form-paramProductsEXCEL');

        return Excel::download(new ProductsExport($categoryId), 'products.xlsx');
    }

    public function downloadProductsPdf(): BinaryFileResponse
    {
        $categoryId = (int) ($this->selectedCategory ?: 0);
        $this->dispatch('close-modal', 'modal-form-paramProductsPDF');

        return Excel::download(new ProductsExport($categoryId), 'products.pdf', ExcelWriter::DOMPDF);
    }

    public function downloadCustomersExcel(): BinaryFileResponse
    {
        return Excel::download(new CustomersExport, 'customers.xlsx');
    }

    public function downloadCustomersPdf(): BinaryFileResponse
    {
        return Excel::download(new CustomersExport, 'customers.pdf', ExcelWriter::DOMPDF);
    }

    public function downloadUsersExcel(): BinaryFileResponse
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }

    public function downloadUsersPdf(): BinaryFileResponse
    {
        return Excel::download(new UsersExport, 'users.pdf', ExcelWriter::DOMPDF);
    }

    public function downloadPaymentsExcel(): BinaryFileResponse
    {
        return Excel::download(new PaymentsExport, 'payments.xlsx');
    }

    public function downloadPaymentsPdf(): BinaryFileResponse
    {
        return Excel::download(new PaymentsExport, 'payments.pdf', ExcelWriter::DOMPDF);
    }

    public function downloadStockExcel(): BinaryFileResponse
    {
        return Excel::download(new StockExport, 'stock.xlsx');
    }

    public function downloadStockPdf(): BinaryFileResponse
    {
        return Excel::download(new StockExport, 'stock.pdf', ExcelWriter::DOMPDF);
    }
}
