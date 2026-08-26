<?php

namespace App\Exports\Reports;

use App\Models\Sale;
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

class SalesExport implements FromCollection, ShouldAutoSize, WithCustomStartCell, WithEvents, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Sale::with('saleDetails.product', 'customer', 'user', 'paymentMethod')->get();
    }

    public function headings(): array
    {
        return [
            'Código de Venta',
            'Cliente',
            'Usuario',
            'Total',
            'Monto',
            'Cambio',
            'Fecha de Venta',
            'Método de Pago',
            'Producto',
            'Cantidad',
            'Precio',
            'Subtotal',
        ];
    }

    public function map($sale): array
    {
        $rows = [];

        // Fila principal (datos de la venta)
        $rows[] = [
            $sale->id,
            $sale->customer->name,
            $sale->user->name,
            $sale->total,
            $sale->amount,
            $sale->change,
            $this->formatDate($sale->sale_date),
            $sale->paymentMethod->name,
            '',
            '',
            '',
            '',
        ];

        // Detalles de la venta
        foreach ($sale->saleDetails as $detail) {
            $rows[] = [
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                $detail->product->name,
                $detail->quantity,
                $detail->price,
                $detail->quantity * $detail->price,
            ];
        }

        return $rows;
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
