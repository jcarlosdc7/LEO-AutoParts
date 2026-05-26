<?php

namespace App\Exports;

use App\Models\User;
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

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithCustomStartCell, WithEvents, ShouldAutoSize
{
	public function collection()
	{
		return User::with('role')->get();
	}

	public function headings(): array
	{
		return [
			'Nombre',
			'Email',
			'Email Verificado',
			'Rol',
			'Fecha de Creación',
			'Fecha de Actualización',
		];
	}

	public function map($user): array
	{
		return [
			$user->name,
			$user->email,
			$this->formatDate($user->email_verified_at),
			$user->role->name,
			$this->formatDate($user->created_at),
			$this->formatDate($user->updated_at),
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
