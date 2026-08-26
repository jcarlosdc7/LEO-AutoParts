<?php

namespace App\Exports\Reports;

use App\Models\Customer;
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

class CustomersExport implements FromCollection, ShouldAutoSize, WithCustomStartCell, WithEvents, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Customer::with('customerType')->get();
    }

    public function headings(): array
    {
        return [
            'DNI/RUC',
            'Nombre',
            'Email',
            'Teléfono',
            'Dirección',
            'Ciudad',
            'Tipo de Cliente',
            'Fecha de Creación',
            'Fecha de Actualización',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->dni_ruc,
            $customer->name,
            $customer->email,
            $customer->phone,
            $customer->address,
            $customer->city,
            $customer->customerType ? $customer->customerType->name : 'No especificado',
            $this->formatDate($customer->created_at),
            $this->formatDate($customer->updated_at),
        ];
    }

    private function formatDate($date)
    {
        return $date ? \Carbon\Carbon::parse($date)->format('d-m-Y H:i:s') : 'No verificado';
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
