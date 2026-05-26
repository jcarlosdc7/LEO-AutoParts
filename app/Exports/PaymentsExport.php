<?php

namespace App\Exports;

use App\Models\Sale;
use App\Models\PaymentMethod;
use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Events\AfterSheet;

class PaymentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithCustomStartCell, WithEvents, ShouldAutoSize
{
	public function collection()
	{
		return Sale::with('customer', 'paymentMethod')->get();
	}

	public function headings(): array
	{
		return [
			'Cliente',
			'Monto Total',
			'Fecha de Venta',
			'Método de Pago',
		];
	}

	public function map($sale): array
	{
		return [
			$sale->customer->name,
			$sale->total,
			$this->formatDate($sale->sale_date),
			$sale->paymentMethod->name,
		];
	}

	private function formatDate($date)
	{
		return \Carbon\Carbon::parse($date)->format('d-m-Y H:i:s');
	}

	public function styles(Worksheet $sheet)
	{
		return [
			1 => [
				'font' => ['bold' => true, 'size' => 12],
				'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'ff00ff7f']],
				'alignment' => [
					'horizontal' => Alignment::HORIZONTAL_CENTER,
					'vertical' => Alignment::VERTICAL_CENTER
				],
			],
		];
	}

	public function startCell(): string
	{
		return 'A1';
	}

	public function registerEvents(): array
	{
		return [];
	}
}
