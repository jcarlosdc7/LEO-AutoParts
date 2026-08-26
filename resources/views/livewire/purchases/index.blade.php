<div class="leo-page"><div class="leo-container">
    <header class="leo-header">
        <div>
            <h1 class="leo-title">Compras</h1>
            <p class="leo-subtitle">Registre entradas, costos y saldos con proveedores.</p>
        </div>
        <div class="rounded-xl bg-slate-950 px-4 py-2 text-right text-white shadow-sm dark:bg-blue-600">
            <span class="block text-xs uppercase text-gray-300">Total actual</span>
            <span class="text-xl font-black">$ {{ number_format($this->total, 2) }}</span>
        </div>
    </header>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="leo-card overflow-hidden">
            <div class="leo-card-header"><h2 class="font-bold text-slate-950">Productos de la compra</h2><p class="text-sm text-slate-500">Seleccione las piezas, cantidades y costos de entrada.</p></div>
            <div class="grid gap-2 bg-gray-100 p-3 md:grid-cols-[minmax(0,1fr)_6rem_8rem_auto]">
                <div>
                    <x-input-label>Producto</x-input-label>
                    <x-select wire:model="productId" class="h-10 w-full text-sm">
                        <option value="">Seleccione...</option>
                        @foreach($products as $product)<option value="{{ $product->id }}">{{ $product->code }} · {{ $product->name }}</option>@endforeach
                    </x-select>
                    @error('productId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div><x-input-label>Cantidad</x-input-label><x-text-input type="number" min="1" wire:model="quantity" class="h-10 w-full text-sm" /></div>
                <div><x-input-label>Costo unitario</x-input-label><x-text-input type="number" min="0.01" step="0.01" wire:model="unitCost" class="h-10 w-full text-sm" /></div>
                <div class="flex items-end"><x-primary-button type="button" wire:click="addItem" class="h-10 bg-gray-900">Agregar</x-primary-button></div>
            </div>
            @error('items') <div class="mx-3 mt-2 rounded bg-red-100 p-2 text-sm text-red-700">{{ $message }}</div> @enderror
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead><tr>
                        <th class="border border-gray-300 bg-gray-900 px-3 py-2 text-left text-sm text-white">Código</th>
                        <th class="border border-gray-300 bg-gray-900 px-3 py-2 text-left text-sm text-white">Producto</th>
                        <th class="border border-gray-300 bg-gray-900 px-3 py-2 text-right text-sm text-white">Cantidad</th>
                        <th class="border border-gray-300 bg-gray-900 px-3 py-2 text-right text-sm text-white">Costo</th>
                        <th class="border border-gray-300 bg-gray-900 px-3 py-2 text-right text-sm text-white">Subtotal</th>
                        <th class="border border-gray-300 bg-gray-900 px-3 py-2 text-center text-sm text-white">Acción</th>
                    </tr></thead>
                    <tbody>
                        @forelse($items as $index => $item)
                            <tr class="hover:bg-gray-100">
                                <td class="border-b px-3 py-2 text-sm">{{ $item['code'] }}</td><td class="border-b px-3 py-2 text-sm">{{ $item['name'] }}</td>
                                <td class="border-b px-3 py-2 text-right text-sm">{{ $item['quantity'] }}</td><td class="border-b px-3 py-2 text-right text-sm">$ {{ number_format($item['unit_cost'], 2) }}</td>
                                <td class="border-b px-3 py-2 text-right text-sm font-semibold">$ {{ number_format($item['total'], 2) }}</td>
                                <td class="border-b px-3 py-2 text-center"><button type="button" wire:click="removeItem({{ $index }})" class="rounded-full bg-red-600 px-3 py-1 text-xs font-bold text-white hover:bg-red-700">Eliminar</button></td>
                            </tr>
                        @empty<tr><td colspan="6" class="p-8 text-center text-sm text-gray-500">Agregue productos para preparar la compra.</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <form wire:submit="save" class="leo-card h-fit p-5 xl:sticky xl:top-20">
            <h2 class="mb-4 border-b border-slate-200 pb-3 font-bold text-slate-950 dark:border-slate-800">Proveedor y pago</h2>
            <div class="mb-3"><x-input-label>Proveedor</x-input-label><x-select wire:model="supplierId" class="h-10 w-full text-sm"><option value="">Seleccione...</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>@endforeach</x-select>@error('supplierId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror</div>
            <div class="grid grid-cols-2 gap-2">
                <div><x-input-label>Pago realizado</x-input-label><x-text-input type="number" min="0" step="0.01" wire:model="amountPaid" class="h-10 w-full text-sm" /></div>
                <div><x-input-label>Vencimiento</x-input-label><x-text-input type="date" wire:model="dueDate" class="h-10 w-full text-sm" /></div>
            </div>
            <div class="mt-3"><x-input-label>Notas</x-input-label><x-textarea wire:model="notes" class="h-24 w-full text-sm" /></div>
            <x-primary-button class="mt-4 w-full justify-center bg-green-600 hover:bg-green-700" wire:loading.attr="disabled">Registrar compra</x-primary-button>
        </form>
    </div>

    <section class="leo-table-wrap">
        <div class="leo-card-header"><h2 class="font-bold text-slate-950">Historial de compras</h2><p class="text-sm text-slate-500">Entradas y saldos registrados con proveedores.</p></div>
        <div class="overflow-x-auto"><table class="min-w-full bg-white">
            <thead><tr>@foreach(['No. compra','Proveedor','Responsable','Fecha','Total','Pagado','Saldo','Estado'] as $title)<th class="border border-gray-300 bg-gray-900 px-3 py-2 text-sm text-white">{{ $title }}</th>@endforeach</tr></thead>
            <tbody>@forelse($purchases as $purchase)<tr class="hover:bg-gray-100">
                <td class="border-b px-3 py-2 text-sm font-semibold">{{ $purchase->purchase_number }}</td><td class="border-b px-3 py-2 text-sm">{{ $purchase->supplier->name }}</td>
                <td class="border-b px-3 py-2 text-sm">{{ $purchase->user->name }}</td><td class="border-b px-3 py-2 text-center text-sm">{{ $purchase->purchase_date->format('d/m/Y H:i') }}</td>
                <td class="border-b px-3 py-2 text-right text-sm">$ {{ number_format($purchase->total, 2) }}</td><td class="border-b px-3 py-2 text-right text-sm">$ {{ number_format($purchase->amount_paid, 2) }}</td>
                <td class="border-b px-3 py-2 text-right text-sm font-semibold">$ {{ number_format($purchase->balance, 2) }}</td><td class="border-b px-3 py-2 text-center"><span class="rounded-full px-2 py-1 text-xs font-bold {{ $purchase->status === 'paid' ? 'bg-green-200 text-green-700' : 'bg-yellow-200 text-yellow-800' }}">{{ $purchase->status === 'paid' ? 'Pagada' : 'Pendiente' }}</span></td>
            </tr>@empty<tr><td colspan="8" class="p-8 text-center text-sm text-gray-500">Aún no hay compras registradas.</td></tr>@endforelse</tbody>
        </table></div>
        <div class="p-3">{{ $purchases->links('custom-tailwind') }}</div>
    </section>

    @script<script>$wire.on('purchaseSaved', ({ number }) => Swal.fire({title:'Compra registrada', text:number, icon:'success', confirmButtonColor:'#111827'}));</script>@endscript
</div></div>
