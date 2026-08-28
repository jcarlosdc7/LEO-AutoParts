<div class="m-2 min-h-[calc(100vh-64px)] overflow-hidden rounded-lg bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3 p-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Inventario</h2>
            <p class="text-sm text-gray-600">Existencias materializadas y Kardex auditable del almacén principal.</p>
        </div>
        @if (auth()->user()?->hasRole('Administrador'))
            <x-primary-button wire:click="create">Crear producto</x-primary-button>
        @endif
    </div>

    <div class="grid gap-3 border-y p-4 md:grid-cols-4">
        <div><x-input-label value="Buscar" /><x-text-input class="w-full" placeholder="Código, nombre, marca o categoría" wire:model.live.debounce.300ms="searching" /></div>
        <div><x-input-label value="Estado" /><select class="w-full rounded border-gray-300" wire:model.live="statusFilter"><option value="all">Todos</option><option value="active">Activos</option><option value="inactive">Inactivos</option></select></div>
        <div><x-input-label value="Existencias" /><select class="w-full rounded border-gray-300" wire:model.live="stockFilter"><option value="all">Todas</option><option value="low">Stock bajo</option><option value="out">Sin stock</option></select></div>
        <div><x-input-label value="Categoría" /><select class="w-full rounded border-gray-300" wire:model.live="categoryFilter"><option value="">Todas</option>@foreach ($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
    </div>

    <div class="overflow-x-auto p-4">
        <table class="min-w-full border-collapse">
            <thead><tr class="bg-gray-900 text-sm text-white"><th class="border p-2 text-left">Código</th><th class="border p-2 text-left">Producto</th><th class="border p-2 text-left">Categoría</th><th class="border p-2 text-right">Stock</th><th class="border p-2 text-right">Mínimo</th><th class="border p-2 text-center">Estado</th><th class="border p-2 text-center">Acciones</th></tr></thead>
            <tbody>
                @forelse ($products as $product)
                    <tr class="hover:bg-gray-50" wire:key="product-{{ $product->id }}">
                        <td class="border-b p-2">{{ $product->code }}</td>
                        <td class="border-b p-2"><div class="font-semibold">{{ $product->name }}</div><div class="text-xs text-gray-500">{{ $product->brand }} · {{ $product->model }} · {{ $product->supplier?->name }}</div></td>
                        <td class="border-b p-2">{{ $product->category?->name }}</td>
                        <td class="border-b p-2 text-right font-bold {{ $product->stock === 0 ? 'text-red-700' : ($product->stock <= $product->min_stock ? 'text-amber-700' : 'text-emerald-700') }}">{{ $product->stock }}</td>
                        <td class="border-b p-2 text-right">{{ $product->min_stock }}</td>
                        <td class="border-b p-2 text-center"><span class="rounded-full px-2 py-1 text-xs {{ $product->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-700' }}">{{ $product->is_active ? 'Activo' : 'Inactivo' }}</span></td>
                        <td class="border-b p-2"><div class="flex flex-wrap justify-center gap-2">
                            <button class="font-semibold text-sky-700" wire:click="showKardex({{ $product->id }})">Kardex</button>
                            @if (auth()->user()?->hasRole('Administrador'))
                                @if ($product->is_active)
                                    <button class="font-semibold text-amber-700" wire:click="requestAdjustment({{ $product->id }})">Ajustar</button>
                                    <button class="font-semibold text-gray-700" wire:click="editProduct({{ $product->id }})">Editar</button>
                                    <button class="font-semibold text-red-700" wire:click="archive({{ $product->id }})" wire:confirm="El producto se archivará pero conservará todo su historial. ¿Continuar?">Archivar</button>
                                @else
                                    <button class="font-semibold text-emerald-700" wire:click="reactivate({{ $product->id }})" wire:confirm="¿Reactivar este producto?">Reactivar</button>
                                @endif
                            @endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-8 text-center text-gray-500">No hay productos para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 pb-4">{{ $products->links('custom-tailwind') }}</div>

    <form wire:submit="save">
        <x-dialog-modal name="modal-form-product" maxWidth="2xl">
            <x-slot name="title">{{ $isEditing ? 'Editar producto' : 'Crear producto' }}</x-slot>
            <x-slot name="content">
                <div class="grid gap-4 md:grid-cols-2">
                    <div><x-input-label value="Código / SKU" /><x-text-input class="w-full" wire:model="product.code" /><x-input-error :messages="$errors->get('product.code')" /></div>
                    <div><x-input-label value="Nombre" /><x-text-input class="w-full" wire:model="product.name" /><x-input-error :messages="$errors->get('product.name')" /></div>
                    <div class="md:col-span-2"><x-input-label value="Descripción" /><x-textarea class="w-full" wire:model="product.description" /><x-input-error :messages="$errors->get('product.description')" /></div>
                    <div><x-input-label value="Marca" /><x-text-input class="w-full" wire:model="product.brand" /><x-input-error :messages="$errors->get('product.brand')" /></div>
                    <div><x-input-label value="Modelo" /><x-text-input class="w-full" wire:model="product.model" /><x-input-error :messages="$errors->get('product.model')" /></div>
                    <div><x-input-label value="Categoría" /><select class="w-full rounded border-gray-300" wire:model="product.category_id">@foreach ($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select><x-input-error :messages="$errors->get('product.category_id')" /></div>
                    <div><x-input-label value="Proveedor activo" /><select class="w-full rounded border-gray-300" wire:model="product.supplier_id">@foreach ($suppliers as $supplier)<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>@endforeach</select><x-input-error :messages="$errors->get('product.supplier_id')" /></div>
                    <div><x-input-label value="Stock mínimo" /><x-text-input type="number" min="0" max="1000000" class="w-full" wire:model="product.min_stock" /><x-input-error :messages="$errors->get('product.min_stock')" /></div>
                    <div><x-input-label value="Precio" /><x-text-input inputmode="decimal" class="w-full" wire:model="product.price" /><x-input-error :messages="$errors->get('product.price')" /></div>
                    <div class="md:col-span-2"><x-input-label value="Imagen JPG/PNG (máx. 4 MB)" /><input type="file" wire:model="newImagePath" wire:key="{{ $newImagePathKey }}"><x-input-error :messages="$errors->get('newImagePath')" /></div>
                    <p class="md:col-span-2 text-sm text-gray-600">El stock no se edita aquí. Use “Ajustar” para registrar un movimiento auditable.</p>
                </div>
            </x-slot>
            <x-slot name="footer"><x-secondary-button wire:click="$dispatch('close-modal', 'modal-form-product')">Cancelar</x-secondary-button><x-primary-button class="ms-3" type="submit">Guardar</x-primary-button></x-slot>
        </x-dialog-modal>
    </form>

    <x-dialog-modal name="modal-adjust-inventory" maxWidth="lg">
        <x-slot name="title">Confirmar ajuste de inventario</x-slot>
        <x-slot name="content">
            @php($adjustmentProduct = $adjustmentProductId ? \App\Models\Product::find($adjustmentProductId) : null)
            @if ($adjustmentProduct)
                <div class="mb-4 rounded bg-gray-100 p-3"><strong>{{ $adjustmentProduct->code }} · {{ $adjustmentProduct->name }}</strong><div>Stock actual: {{ $adjustmentProduct->stock }} · Stock resultante: <strong>{{ $this->adjustmentPreview() }}</strong></div></div>
            @endif
            <div class="space-y-4">
                <div><x-input-label value="Operación" /><select class="w-full rounded border-gray-300" wire:model.live="adjustmentMode"><option value="increase">Entrada por ajuste</option><option value="decrease">Salida por ajuste</option><option value="count">Corrección por conteo físico</option></select></div>
                <div><x-input-label :value="$adjustmentMode === 'count' ? 'Conteo físico' : 'Cantidad'" /><x-text-input type="number" min="0" max="1000000" class="w-full" wire:model.live="adjustmentValue" /><x-input-error :messages="$errors->get('adjustmentValue')" /></div>
                <div><x-input-label value="Motivo obligatorio (10–1000 caracteres)" /><x-textarea rows="4" class="w-full" wire:model="adjustmentReason" /><x-input-error :messages="$errors->get('adjustmentReason')" /></div>
                @if ($this->adjustmentPreview() < 0)<p class="font-semibold text-red-700">El resultado sería negativo y la operación será rechazada.</p>@endif
            </div>
        </x-slot>
        <x-slot name="footer"><x-secondary-button wire:click="$dispatch('close-modal', 'modal-adjust-inventory')">Cancelar</x-secondary-button><x-danger-button class="ms-3" wire:click="processAdjustment" wire:loading.attr="disabled">Confirmar ajuste</x-danger-button></x-slot>
    </x-dialog-modal>

    <x-dialog-modal name="modal-kardex" maxWidth="3xl">
        <x-slot name="title">Kardex {{ $kardexProduct ? '· '.$kardexProduct->code.' '.$kardexProduct->name : '' }}</x-slot>
        <x-slot name="content">
            <div class="mb-4 grid gap-3 md:grid-cols-3"><div><x-input-label value="Desde" /><x-text-input type="date" class="w-full" wire:model="kardexFrom" /></div><div><x-input-label value="Hasta" /><x-text-input type="date" class="w-full" wire:model="kardexTo" /></div><div class="flex items-end"><x-primary-button wire:click="applyKardexDates">Aplicar rango</x-primary-button></div><x-input-error :messages="$errors->get('kardexTo')" /></div>
            <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead><tr class="bg-gray-900 text-white"><th class="p-2">Fecha</th><th class="p-2">Documento</th><th class="p-2">Tipo</th><th class="p-2 text-right">Entrada</th><th class="p-2 text-right">Salida</th><th class="p-2 text-right">Saldo</th><th class="p-2">Usuario</th><th class="p-2">Almacén</th><th class="p-2">Motivo</th></tr></thead><tbody>
                @if ($kardexFrom !== '')<tr class="bg-sky-50 font-semibold"><td class="p-2">{{ $kardexFrom }}</td><td class="p-2" colspan="4">Saldo inicial del período</td><td class="p-2 text-right">{{ $kardexOpening }}</td><td class="p-2" colspan="3"></td></tr>@endif
                @forelse ($kardexMovements ?? [] as $movement)<tr class="border-b"><td class="whitespace-nowrap p-2">{{ $movement->occurred_at?->format('Y-m-d H:i:s') }}</td><td class="p-2">{{ $movement->reference_type ? class_basename($movement->reference_type).' #'.$movement->reference_id : 'Baseline' }}</td><td class="p-2">{{ strtoupper(str_replace('_', ' ', $movement->type)) }}</td><td class="p-2 text-right text-emerald-700">{{ $movement->quantity > 0 ? $movement->quantity : '' }}</td><td class="p-2 text-right text-red-700">{{ $movement->quantity < 0 ? abs($movement->quantity) : '' }}</td><td class="p-2 text-right font-bold">{{ $movement->stock_after }}</td><td class="p-2">{{ $movement->actor?->name ?? 'Sistema' }}</td><td class="p-2">{{ $movement->warehouse?->code }}</td><td class="max-w-xs p-2">{{ $movement->notes }}</td></tr>@empty<tr><td colspan="9" class="p-6 text-center text-gray-500">No hay movimientos para el rango.</td></tr>@endforelse
            </tbody></table></div>
            @if ($kardexMovements)<div class="mt-4">{{ $kardexMovements->links('custom-tailwind') }}</div>@endif
        </x-slot>
        <x-slot name="footer"><x-secondary-button wire:click="$dispatch('close-modal', 'modal-kardex')">Cerrar</x-secondary-button><x-primary-button class="ms-3" wire:click="exportKardex">Exportar rango XLSX</x-primary-button></x-slot>
    </x-dialog-modal>
</div>
