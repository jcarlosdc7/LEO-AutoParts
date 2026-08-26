<div class="leo-page">
    <div class="leo-container">
        <header class="leo-header">
            <div><h1 class="leo-title">Historial de ventas</h1><p class="leo-subtitle">Consulte comprobantes, clientes, pagos y estados.</p></div>
            <a href="{{ route('invoicing') }}" class="leo-button-primary">+ Nueva venta</a>
        </header>
        <section class="leo-table-wrap">
            <table class="leo-table">
                <thead><tr>
                    @foreach(['invoice_number'=>'Comprobante','customer_id'=>'Cliente','user_id'=>'Vendedor','total'=>'Total','sale_date'=>'Fecha'] as $column=>$label)
                        <th><button wire:click="sortBy('{{ $column }}')" class="flex items-center gap-1">{{ $label }} @if($sortColumn===$column)<span>{{ $sortDirection==='asc'?'↑':'↓' }}</span>@endif</button></th>
                    @endforeach
                    <th>Método</th><th>Estado</th><th class="text-center">Detalle</th>
                </tr></thead>
                <tbody>@forelse($sales as $sale)
                    <tr>
                        <td class="font-bold text-slate-950">{{ $sale->invoice_number ?? '#'.$sale->id }}</td>
                        <td><p class="font-medium text-slate-900">{{ $sale->customer_name_snapshot ?? $sale->customer?->name ?? 'Sin cliente' }}</p><p class="text-xs text-slate-500">{{ $sale->customer_document_snapshot }}</p></td>
                        <td>{{ $sale->user?->name ?? '—' }}</td><td class="text-right font-bold text-blue-600">$ {{ number_format($sale->total,2) }}</td>
                        <td class="whitespace-nowrap">{{ $sale->sale_date?->format('d/m/Y H:i') ?? $sale->sale_date }}</td><td>{{ $sale->paymentMethod?->name ?? '—' }}</td>
                        <td><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $sale->status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $sale->status === 'cancelled' ? 'Anulada' : 'Contabilizada' }}</span></td>
                        <td class="text-center"><button wire:click="view({{ $sale->id }})" class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700 hover:bg-blue-100">Ver</button></td>
                    </tr>
                @empty<tr><td colspan="8" class="py-16 text-center text-slate-500">Aún no hay ventas registradas.</td></tr>@endforelse</tbody>
            </table>
        </section>
        <div>{{ $sales->links('custom-tailwind') }}</div>
    </div>

    <x-dialog-modal name="modal-sale-detail" maxWidth="4xl">
        <x-slot name="title">
            <div><h2 class="text-xl font-bold text-slate-950">{{ $selectedSale?->invoice_number ?? 'Detalle de venta' }}</h2><p class="text-sm text-slate-500">Información preservada de la operación.</p></div>
        </x-slot>
        <x-slot name="content">
            @if($selectedSale)
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">Cliente</p><p class="font-semibold">{{ $selectedSale->customer_name_snapshot ?? $selectedSale->customer?->name }}</p></div>
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">Vendedor</p><p class="font-semibold">{{ $selectedSale->user?->name }}</p></div>
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">Fecha</p><p class="font-semibold">{{ $selectedSale->sale_date?->format('d/m/Y H:i') }}</p></div>
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">Método</p><p class="font-semibold">{{ $selectedSale->paymentMethod?->name }}</p></div>
                </div>
                <div class="mt-5 overflow-x-auto rounded-xl border border-slate-200"><table class="leo-table"><thead><tr><th>Producto</th><th class="text-right">Cantidad</th><th class="text-right">Precio</th><th class="text-right">Subtotal</th></tr></thead><tbody>
                    @foreach($saleDetails as $detail)<tr><td><p class="font-semibold text-slate-900">{{ $detail->product_name_snapshot ?? $detail->product?->name }}</p><p class="text-xs text-slate-500">{{ $detail->product_code_snapshot }}</p></td><td class="text-right">{{ $detail->quantity }}</td><td class="text-right">$ {{ number_format($detail->price,2) }}</td><td class="text-right font-bold">$ {{ number_format($detail->total,2) }}</td></tr>@endforeach
                </tbody></table></div>
                <div class="mt-4 flex justify-end"><div class="rounded-xl bg-slate-950 px-5 py-3 text-white"><span class="text-sm text-slate-300">Total</span><span class="ml-3 text-xl font-black">$ {{ number_format($selectedSale->total,2) }}</span></div></div>
            @endif
        </x-slot>
        <x-slot name="footer"><button wire:click="$dispatch('close-modal','modal-sale-detail')" class="leo-button-dark">Cerrar</button></x-slot>
    </x-dialog-modal>
</div>
