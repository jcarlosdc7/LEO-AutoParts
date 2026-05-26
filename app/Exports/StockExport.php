<?php

namespace App\Exports;

use App\Models\Product;
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

class StockExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithCustomStartCell, WithEvents, ShouldAutoSize
{
	public function collection()
	{
		return Product::with('supplier', 'category')->get();
	}

	public function headings(): array
	{
		return [
			'Código',
			'Nombre',
			'Stock Actual',
			'Stock Mínimo',
		];
	}

	public function map($product): array
	{
		if($product->stock === 0)
			$value = 'NO STOCK';
		else
			$value = $product->stock;

		return [
			$product->code,
			$product->name,
			$value,
			$product->min_stock,
		];
	}

	private function formatDate($date)
	{
		return $date ? \Carbon\Carbon::parse($date)->format('d-m-Y H:i:s') : 'No disponible';
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
