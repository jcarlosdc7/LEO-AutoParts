@php
$role = auth()->user()->role?->name;
$sections = [
    ['label' => 'Principal', 'items' => [
        ['route'=>'dashboard','label'=>'Resumen','roles'=>null],
        ['route'=>'catalog','label'=>'Catálogo','roles'=>null],
        ['route'=>'customers','label'=>'Clientes','roles'=>null],
        ['route'=>'invoicing','label'=>'Nueva venta','roles'=>null],
    ]],
    ['label' => 'Operaciones', 'items' => [
        ['route'=>'salesHistory','label'=>'Historial de ventas','roles'=>['Administrador','Contador']],
        ['route'=>'inventory','label'=>'Inventario','roles'=>['Administrador','Contador']],
        ['route'=>'kardex','label'=>'Kardex','roles'=>['Administrador','Contador']],
        ['route'=>'purchases','label'=>'Compras','roles'=>['Administrador','Contador']],
        ['route'=>'cash','label'=>'Caja','roles'=>['Administrador','Contador']],
        ['route'=>'suppliers','label'=>'Proveedores','roles'=>['Administrador','Contador']],
        ['route'=>'reports','label'=>'Reportes','roles'=>['Administrador','Contador']],
    ]],
    ['label' => 'Administración', 'items' => [
        ['route'=>'users','label'=>'Usuarios','roles'=>['Administrador']],
        ['route'=>'configuration','label'=>'Configuración','roles'=>['Administrador']],
    ]],
];
@endphp
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed bottom-0 left-0 top-16 z-40 flex w-64 flex-col border-r border-slate-800 bg-slate-950 text-slate-200 shadow-2xl transition-transform duration-300 dark:border-slate-700 dark:bg-slate-900 lg:translate-x-0 lg:shadow-none">
    <div class="flex-1 overflow-y-auto px-3 py-5">
        @foreach($sections as $section)
            @php($visible = collect($section['items'])->filter(fn($item) => $item['roles'] === null || in_array($role, $item['roles'])))
            @if($visible->isNotEmpty())
                <p class="mb-2 mt-4 px-3 text-[10px] font-bold uppercase tracking-[0.22em] text-slate-500 first:mt-0">{{ $section['label'] }}</p>
                <nav class="space-y-1">
                    @foreach($visible as $item)
                        @php($active = request()->routeIs($item['route']))
                        <a href="{{ route($item['route']) }}" wire:navigate @click="sidebarOpen = false" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ $active ? 'bg-blue-600 text-white shadow-md shadow-blue-950/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white dark:hover:bg-slate-800/80' }}">
                            <span class="flex h-5 w-5 shrink-0 items-center justify-center {{ $active ? 'text-white' : 'text-slate-500 group-hover:text-blue-400' }}">
                                @switch($item['route'])
                                    @case('dashboard')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M4 13h6V4H4v9Zm0 7h6v-4H4v4Zm10 0h6v-9h-6v9Zm0-13h6V4h-6v3Z"/></svg>@break
                                    @case('catalog')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="m4 7 8-4 8 4-8 4-8-4Zm0 5 8 4 8-4M4 17l8 4 8-4"/></svg>@break
                                    @case('customers')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm13 10v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>@break
                                    @case('invoicing')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M6 2h9l4 4v16H6V2Zm9 0v5h5M9 12h6m-6 4h6"/></svg>@break
                                    @case('salesHistory')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 1 0 3-6.7L3 8m0-5v5h5m4-1v5l3 2"/></svg>@break
                                    @case('inventory')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M3 6h18v15H3V6Zm3-3h12v3M8 10h8M8 14h3"/></svg>@break
                                    @case('kardex')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M9 5h11M9 12h11M9 19h11M4 5h.01M4 12h.01M4 19h.01"/></svg>@break
                                    @case('purchases')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l2.4 11.5A2 2 0 0 0 9.35 16H18a2 2 0 0 0 1.94-1.5L21 8H6m4 12h.01M18 20h.01"/></svg>@break
                                    @case('cash')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M3 6h18v12H3V6Zm4 3h.01M17 15h.01M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg>@break
                                    @case('suppliers')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h6m-6 4h6m-6 4h6"/></svg>@break
                                    @case('reports')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M4 19V9m5 10V5m5 14v-7m5 7V3"/></svg>@break
                                    @case('users')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>@break
                                    @case('configuration')<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm8.5-3.5 1.5 2-2 3-2.4-.4-1.4 1.4.4 2.4-3 1.6-2-1.5-2 1.5-3-1.6.4-2.4-1.4-1.4-2.4.4-2-3 1.5-2-1.5-2 2-3 2.4.4L8 6l-.4-2.4 3-1.6 2 1.5 2-1.5 3 1.6-.4 2.4 1.4 1.4 2.4-.4 2 3-1.5 2Z"/></svg>@break
                                @endswitch
                            </span>
                            <span>{{ $item['label'] }}</span>
                            @if($active)<span class="ml-auto text-xs opacity-70">●</span>@endif
                        </a>
                    @endforeach
                </nav>
            @endif
        @endforeach
    </div>
    <div class="border-t border-slate-800 p-4 dark:border-slate-700">
        <div class="rounded-xl bg-slate-900 p-3 dark:bg-slate-950"><div class="flex items-center gap-3"><img src="{{ asset('images/brand/icon-light.png') }}" alt="" class="h-9 w-9 object-contain"><div><p class="text-xs font-semibold text-white">LEO AutoParts</p><p class="mt-0.5 text-[11px] text-slate-500">Inventario inteligente</p></div></div></div>
    </div>
</aside>
