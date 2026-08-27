<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromCollection, ShouldAutoSize, WithCustomStartCell, WithEvents, WithHeadings, WithMapping, WithStyles
{
    protected $filter;

    // Constructor para recibir parámetros
    public function __construct($filter = null)
    {
        $this->filter = $filter;
    }

    public function collection()
    {
        // dd($this->filter);
        $query = Product::with('supplier', 'category');

        if ($this->filter != 0) {
            $query->where('category_id', $this->filter);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Código',
            'Nombre',
            'Descripción',
            'Marca',
            'Modelo',
            'Proveedor',
            'Categoría',
            'Stock',
            'Stock Mínimo',
            'Precio',
            'Ruta de Imagen',
            'Fecha de Creación',
            'Fecha de Actualización',
        ];
    }

    public function map($product): array
    {
        return [
            $product->code,
            $product->name,
            $product->description,
            $product->brand,
            $product->model,
            $product->supplier->name,
            $product->category->name,
            $product->stock,
            $product->min_stock,
            $product->price,
            $product->image_path,
            $product->created_at->format('d-m-Y H:i:s'), // Formato de fecha mejorado
            $product->updated_at->format('d-m-Y H:i:s'), // Formato de fecha mejorado
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'ff00ff7f']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
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
