<div class="leo-page">
    <div class="leo-container">
        <header class="leo-header">
            <div><h1 class="leo-title">Inventario</h1><p class="leo-subtitle">Existencias, precios y estado de cada repuesto.</p></div>
            <x-primary-button wire:click="create" class="leo-button-dark">+ Nuevo producto</x-primary-button>
        </header>

        <section class="leo-card p-4">
            <div class="grid gap-3 md:grid-cols-[10rem_minmax(0,1fr)_auto] md:items-end">
                <div><x-input-label>Buscar por</x-input-label><x-select wire:model.live="searchMode" class="leo-field">@foreach($fields as $field)<option>{{ $field }}</option>@endforeach</x-select></div>
                <div><x-input-label>Consulta</x-input-label><x-text-input wire:model.live.debounce.300ms="searching" class="leo-field" placeholder="Escriba nombre, código o marca..." /></div>
                <div class="flex rounded-xl bg-slate-100 p-1">
                    <button wire:click="setListMode" class="rounded-lg px-4 py-2 text-sm font-semibold transition {{ $viewMode === 'list' ? 'bg-white text-slate-950 shadow-sm' : 'text-slate-500' }}">Lista</button>
                    <button wire:click="setCardMode" class="rounded-lg px-4 py-2 text-sm font-semibold transition {{ $viewMode === 'card' ? 'bg-white text-slate-950 shadow-sm' : 'text-slate-500' }}">Tarjetas</button>
                </div>
            </div>
        </section>

        @if($viewMode === 'list')
        <section class="leo-table-wrap">
            <table class="leo-table">
                <thead><tr><th>Producto</th><th>Proveedor</th><th>Categoría</th><th class="text-right">Existencia</th><th class="text-right">Costo</th><th class="text-right">Venta</th><th class="text-center">Acciones</th></tr></thead>
                <tbody>@forelse($products as $item)
                    <tr>
                        <td><div class="flex items-center gap-3"><div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-xs font-bold text-slate-500">{{ mb_substr($item->brand,0,2) }}</div><div><p class="font-semibold text-slate-900">{{ $item->name }}</p><p class="text-xs text-slate-500">{{ $item->code }} · {{ $item->brand }} {{ $item->model }}</p></div></div></td>
                        <td>{{ $item->supplier?->name ?? '—' }}</td><td>{{ $item->category?->name ?? '—' }}</td>
                        <td class="text-right"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $item->stock <= $item->min_stock ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $item->stock }} unidades</span></td>
                        <td class="text-right text-slate-500">$ {{ number_format($item->cost_price ?? 0, 2) }}</td><td class="text-right font-bold text-slate-950">$ {{ number_format($item->price, 2) }}</td>
                        <td><div class="flex justify-center gap-2"><button wire:click="view({{ $item->id }})" class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700 hover:bg-blue-100">Ver</button><button wire:click="update({{ $item->id }})" class="rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700 hover:bg-amber-100">Editar</button><button wire:click="$dispatch('confirmDeleteProduct',{id:{{ $item->id }}})" class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700 hover:bg-red-100">Eliminar</button></div></td>
                    </tr>
                @empty<tr><td colspan="7" class="py-16 text-center text-slate-500">No hay productos que coincidan con la búsqueda.</td></tr>@endforelse</tbody>
            </table>
        </section>
        @else
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @forelse($products as $item)
                <article class="leo-card overflow-hidden">
                    @php
                        $sourcePath = $item->image_path && Storage::disk('public')->exists($item->image_path) ? $item->image_path : 'productImages/_default.jpg';
                        $imageName = pathinfo($sourcePath, PATHINFO_FILENAME);
                        $itemImage = asset("storage/productImages/optimized/{$imageName}.webp");
                        $itemBackdrop = asset("storage/productImages/backdrops/{$imageName}.webp");
                    @endphp
                    <div class="leo-product-media relative flex aspect-square items-center justify-center overflow-hidden p-5" style="--leo-product-backdrop: url('{{ $itemBackdrop }}')">
                        <img src="{{ $itemImage }}" alt="{{ $item->name }}" loading="lazy" decoding="async" width="512" height="512" class="relative z-10 h-full w-full object-contain" />
                        <span class="absolute right-3 top-3 rounded-full px-2 py-1 text-xs font-bold {{ $item->stock <= $item->min_stock ? 'bg-red-100 text-red-700' : 'bg-white text-emerald-700 shadow-sm' }}">Stock {{ $item->stock }}</span>
                    </div>
                    <div class="p-4"><p class="text-xs font-bold uppercase tracking-wide text-blue-600">{{ $item->code }}</p><h2 class="mt-1 truncate font-bold text-slate-950">{{ $item->name }}</h2><p class="mt-1 truncate text-sm text-slate-500">{{ $item->brand }} · {{ $item->model }}</p><div class="mt-4 flex items-center justify-between"><span class="text-xl font-black text-slate-950">$ {{ number_format($item->price,2) }}</span><div class="flex gap-2"><button wire:click="view({{ $item->id }})" class="text-sm font-bold text-blue-600">Ver</button><button wire:click="update({{ $item->id }})" class="text-sm font-bold text-amber-600">Editar</button><button wire:click="$dispatch('confirmDeleteProduct',{id:{{ $item->id }}})" class="text-sm font-bold text-red-600">Eliminar</button></div></div></div>
                </article>
            @empty<p class="col-span-full py-16 text-center text-slate-500">No hay productos que coincidan.</p>@endforelse
        </section>
        @endif
        <div>{{ $products->links('custom-tailwind') }}</div>
    </div>

    <x-dialog-modal name="modal-view-product" maxWidth="lg">
        <x-slot name="title"><div><h2 class="text-xl font-bold text-slate-950">{{ $product->name ?: 'Producto' }}</h2></div></x-slot>
        <x-slot name="content">
            <div class="grid gap-5 sm:grid-cols-[10rem_1fr]">
                <div class="flex h-40 items-center justify-center rounded-2xl bg-slate-100">@if($product->image_path && Storage::disk('public')->exists($product->image_path))<img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-contain p-3" />@else<span class="text-2xl font-black text-slate-400">{{ mb_substr($product->brand ?? 'LE',0,2) }}</span>@endif</div>
                <dl class="grid grid-cols-2 gap-3 text-sm"><div class="col-span-2"><dt class="text-slate-500">Descripción</dt><dd class="font-medium text-slate-900">{{ $product->description }}</dd></div><div><dt class="text-slate-500">Código</dt><dd class="font-semibold">{{ $product->code }}</dd></div><div><dt class="text-slate-500">Marca / modelo</dt><dd>{{ $product->brand }} {{ $product->model }}</dd></div><div><dt class="text-slate-500">Proveedor</dt><dd>{{ $product->supplier?->name ?? '—' }}</dd></div><div><dt class="text-slate-500">Categoría</dt><dd>{{ $product->category?->name ?? '—' }}</dd></div><div><dt class="text-slate-500">Stock</dt><dd class="font-bold">{{ $product->stock }}</dd></div><div><dt class="text-slate-500">Precio</dt><dd class="font-bold">$ {{ number_format($product->price ?? 0,2) }}</dd></div></dl>
            </div>
        </x-slot>
        <x-slot name="footer"><x-primary-button wire:click="$dispatch('close-modal','modal-view-product')" class="leo-button-dark">Cerrar</x-primary-button></x-slot>
    </x-dialog-modal>

    <form wire:submit="save">
        <x-dialog-modal name="modal-form-product" maxWidth="2xl">
            <x-slot name="title"><div><h2 class="text-xl font-bold text-slate-950">{{ $isEditing ? 'Editar producto' : 'Nuevo producto' }}</h2><p class="text-sm text-slate-500">Complete la información comercial y de existencia.</p></div></x-slot>
            <x-slot name="content">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><x-input-label>Código</x-input-label><x-text-input wire:model="product.code" class="leo-field" />@error('product.code')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</div>
                    <div><x-input-label>Nombre</x-input-label><x-text-input wire:model="product.name" class="leo-field" />@error('product.name')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</div>
                    <div class="sm:col-span-2"><x-input-label>Descripción</x-input-label><x-textarea wire:model="product.description" class="leo-field min-h-24" />@error('product.description')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</div>
                    <div><x-input-label>Marca</x-input-label><x-text-input wire:model="product.brand" class="leo-field" /></div><div><x-input-label>Modelo</x-input-label><x-text-input wire:model="product.model" class="leo-field" /></div>
                    <div><x-input-label>Categoría</x-input-label><x-select wire:model="product.category_id" class="leo-field">@foreach($categories as $option)<option value="{{ $option->id }}">{{ $option->name }}</option>@endforeach</x-select></div>
                    <div><x-input-label>Proveedor</x-input-label><x-select wire:model="product.supplier_id" class="leo-field">@foreach($suppliers as $option)<option value="{{ $option->id }}">{{ $option->name }}</option>@endforeach</x-select></div>
                    <div><x-input-label>Stock</x-input-label><x-text-input type="number" min="0" wire:model="product.stock" class="leo-field" /></div><div><x-input-label>Stock mínimo</x-input-label><x-text-input type="number" min="0" wire:model="product.min_stock" class="leo-field" /></div>
                    <div><x-input-label>Costo unitario</x-input-label><x-text-input type="number" min="0" step="0.01" wire:model="product.cost_price" class="leo-field" />@error('product.cost_price')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</div><div><x-input-label>Precio de venta</x-input-label><x-text-input type="number" min="0.01" step="0.01" wire:model="product.price" class="leo-field" /></div><div class="sm:col-span-2"><x-input-label>Imagen</x-input-label><input type="file" wire:model="newImagePath" wire:key="{{ $newImagePathKey }}" class="leo-field p-2" /></div>
                </div>
            </x-slot>
            <x-slot name="footer"><div class="flex gap-2"><button type="button" wire:click="$dispatch('close-modal','modal-form-product')" class="leo-button">Cancelar</button><x-primary-button type="submit" class="leo-button-primary">Guardar producto</x-primary-button></div></x-slot>
        </x-dialog-modal>
    </form>
    @script<script>$wire.on('confirmDeleteProduct',({id})=>{Swal.fire({title:'¿Eliminar producto?',text:'Se ocultará de las operaciones nuevas y se conservará su historial.',icon:'warning',showCancelButton:true,confirmButtonText:'Sí, eliminar',cancelButtonText:'Cancelar',confirmButtonColor:'#dc2626'}).then(r=>{if(r.isConfirmed)$wire.destroy(id)})});$wire.on('productDeleted',()=>Swal.fire({title:'Producto eliminado',icon:'success',timer:1400,showConfirmButton:false}));</script>@endscript
</div>
