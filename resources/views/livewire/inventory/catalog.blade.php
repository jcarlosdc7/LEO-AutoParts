<div class="leo-page"><div class="leo-container">
    <header class="leo-header"><div><h1 class="leo-title">Catálogo de repuestos</h1><p class="leo-subtitle">Consulta precios, disponibilidad y referencias del inventario.</p></div>
        @if(in_array(auth()->user()->role?->name, ['Administrador','Contador']))<a href="{{ route('inventory') }}" wire:navigate class="leo-button-dark">Administrar inventario</a>@endif
    </header>
    <section class="leo-card p-4"><div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_13rem]">
        <div><x-input-label>Buscar repuesto</x-input-label><x-text-input wire:model.live.debounce.300ms="search" class="leo-field" placeholder="Nombre, código, marca o modelo..." /></div>
        <div><x-input-label>Disponibilidad</x-input-label><x-select wire:model.live="availability" class="leo-field"><option value="all">Todos</option><option value="available">Con existencia</option><option value="low">Stock bajo</option></x-select></div>
    </div></section>
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
        @forelse($products as $product)<article class="leo-card overflow-hidden">
            @php
                $sourcePath = $product->image_path && Storage::disk('public')->exists($product->image_path) ? $product->image_path : 'productImages/_default.jpg';
                $imageName = pathinfo($sourcePath, PATHINFO_FILENAME);
                $productImage = asset("storage/productImages/optimized/{$imageName}.webp");
                $productBackdrop = asset("storage/productImages/backdrops/{$imageName}.webp");
            @endphp
            <div class="leo-product-media relative flex aspect-square items-center justify-center overflow-hidden p-5" style="--leo-product-backdrop: url('{{ $productBackdrop }}')">
                <img src="{{ $productImage }}" alt="{{ $product->name }}" loading="lazy" decoding="async" width="512" height="512" class="relative z-10 h-full w-full object-contain" />
                <span class="absolute right-3 top-3 rounded-full px-2.5 py-1 text-xs font-bold {{ $product->stock <= 0 ? 'bg-red-100 text-red-700' : ($product->stock <= $product->min_stock ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">{{ $product->stock <= 0 ? 'Agotado' : $product->stock.' disponibles' }}</span>
            </div><div class="p-4"><p class="text-xs font-bold uppercase tracking-wide text-blue-600">{{ $product->code }}</p><h2 class="mt-1 text-lg font-bold text-slate-950">{{ $product->name }}</h2><p class="mt-1 text-sm text-slate-500">{{ $product->brand }} · {{ $product->model }}</p><div class="mt-5 flex items-end justify-between"><div><p class="text-xs text-slate-400">Precio de venta</p><p class="text-2xl font-black text-slate-950">$ {{ number_format($product->price,2) }}</p></div><a href="{{ route('invoicing') }}" wire:navigate class="rounded-xl bg-blue-50 px-3 py-2 text-sm font-bold text-blue-700 hover:bg-blue-100">Facturar</a></div></div>
        </article>@empty<div class="leo-card col-span-full py-16 text-center"><p class="font-semibold text-slate-700">No encontramos repuestos</p><p class="mt-1 text-sm text-slate-500">Prueba con otra búsqueda o filtro.</p></div>@endforelse
    </section><div>{{ $products->links('custom-tailwind') }}</div>
</div></div>
