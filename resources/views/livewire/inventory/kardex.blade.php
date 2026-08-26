<div class="leo-page"><div class="leo-container">
    <header class="leo-header"><div><h1 class="leo-title">Kardex</h1><p class="leo-subtitle">Trazabilidad completa de entradas, salidas y ajustes.</p></div><a href="{{ route('inventory') }}" class="leo-button-dark">Ver inventario</a></header>
    <section class="grid gap-4 sm:grid-cols-2">
        <article class="leo-card border-l-4 border-l-emerald-500 p-5"><p class="text-sm font-semibold text-slate-500">Entradas filtradas</p><p class="mt-2 text-3xl font-black text-slate-950">+{{ number_format($totalEntries) }}</p></article>
        <article class="leo-card border-l-4 border-l-red-500 p-5"><p class="text-sm font-semibold text-slate-500">Salidas filtradas</p><p class="mt-2 text-3xl font-black text-slate-950">-{{ number_format($totalExits) }}</p></article>
    </section>
    <section class="leo-card p-4"><div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div><x-input-label>Producto</x-input-label><x-select wire:model.live="productId" class="leo-field"><option value="">Todos</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->code }} · {{ $product->name }}</option>@endforeach</x-select></div>
        <div><x-input-label>Tipo</x-input-label><x-select wire:model.live="type" class="leo-field"><option value="">Todos</option><option value="opening_balance">Saldo inicial</option><option value="purchase">Compra</option><option value="sale">Venta</option><option value="void_sale">Anulación de venta</option><option value="adjustment">Ajuste</option></x-select></div>
        <div><x-input-label>Desde</x-input-label><x-text-input type="date" wire:model.live="dateFrom" class="leo-field" /></div><div><x-input-label>Hasta</x-input-label><x-text-input type="date" wire:model.live="dateTo" class="leo-field" /></div>
    </div></section>
    <section class="leo-table-wrap"><table class="leo-table"><thead><tr><th>Fecha</th><th>Producto</th><th>Tipo</th><th class="text-right">Movimiento</th><th class="text-right">Anterior</th><th class="text-right">Final</th><th class="text-right">Costo</th><th>Responsable</th><th>Nota</th></tr></thead>
        <tbody>@forelse($movements as $movement)<tr>
            <td class="whitespace-nowrap">{{ $movement->occurred_at?->format('d/m/Y H:i') }}</td><td><p class="font-semibold text-slate-900">{{ $movement->product?->name }}</p><p class="text-xs text-slate-500">{{ $movement->product?->code }}</p></td>
            <td>@php($labels=['opening_balance'=>'Saldo inicial','purchase'=>'Compra','sale'=>'Venta','void_sale'=>'Anulación','adjustment'=>'Ajuste'])<span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $movement->quantity >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $labels[$movement->type] ?? ucfirst($movement->type) }}</span></td>
            <td class="text-right text-lg font-black {{ $movement->quantity >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}</td><td class="text-right">{{ $movement->stock_before }}</td><td class="text-right font-bold">{{ $movement->stock_after }}</td><td class="text-right">{{ $movement->unit_cost !== null ? '$ '.number_format($movement->unit_cost,2) : '—' }}</td><td>{{ $movement->user?->name ?? 'Sistema' }}</td><td class="max-w-xs truncate text-slate-500" title="{{ $movement->note }}">{{ $movement->note ?? '—' }}</td>
        </tr>@empty<tr><td colspan="9" class="py-16 text-center text-slate-500">No existen movimientos para los filtros seleccionados.</td></tr>@endforelse</tbody>
    </table></section><div>{{ $movements->links('custom-tailwind') }}</div>
</div></div>
</div>
