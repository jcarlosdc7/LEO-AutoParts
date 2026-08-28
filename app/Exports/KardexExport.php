<?php

namespace App\Exports;

use App\Models\StockMovement;
use Carbon\Carbon;
use Generator;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KardexExport implements FromGenerator, ShouldAutoSize, WithHeadings
{
    public function __construct(
        private readonly int $productId,
        private readonly string $from,
        private readonly string $to,
    ) {}

    public function generator(): Generator
    {
        $from = Carbon::parse($this->from)->startOfDay();
        $to = Carbon::parse($this->to)->endOfDay();
        $opening = (int) StockMovement::query()
            ->where('product_id', $this->productId)
            ->where('occurred_at', '<', $from)
            ->sum('quantity');

        yield [$from->format('Y-m-d'), 'Saldo inicial', 'OPENING BALANCE', null, null, $opening, 'Sistema', null, null];

        $movements = StockMovement::query()
            ->with(['warehouse:id,code', 'actor:id,name'])
            ->where('product_id', $this->productId)
            ->whereBetween('occurred_at', [$from, $to])
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->cursor();

        foreach ($movements as $movement) {
            yield [
                $movement->occurred_at->format('Y-m-d H:i:s'),
                $movement->reference_type ? class_basename($movement->reference_type).' #'.$movement->reference_id : 'Baseline',
                strtoupper(str_replace('_', ' ', $movement->type)),
                $movement->quantity > 0 ? $movement->quantity : null,
                $movement->quantity < 0 ? abs($movement->quantity) : null,
                $movement->stock_after,
                $movement->actor?->name ?? 'Sistema',
                $movement->warehouse?->code,
                $movement->notes,
            ];
        }
    }

    public function headings(): array
    {
        return ['Fecha', 'Documento', 'Tipo', 'Entrada', 'Salida', 'Saldo', 'Usuario', 'Almacén', 'Motivo'];
    }
}
