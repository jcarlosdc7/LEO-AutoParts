<div>
    <div x-show="financeSidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-sm lg:hidden" @click="financeSidebarOpen = false"></div>

    <aside :class="financeSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-white/10 bg-gray-900 pt-16 text-white shadow-xl transition-transform duration-300 lg:translate-x-0">
        <div class="border-b border-white/[0.06] px-5 py-5">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-md bg-white text-lg font-black text-gray-900 shadow">L</div>
                <div>
                    <p class="text-sm font-extrabold tracking-wide text-white">LEO AutoParts</p>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-blue-300">Control financiero</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
            <section>
                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Panorama</p>
                <x-finance-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    <x-slot name="icon"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 13h6V4H4v9Zm0 7h6v-3H4v3Zm10 0h6v-9h-6v9Zm0-16v3h6V4h-6Z"/></svg></x-slot>
                    Dashboard financiero
                </x-finance-nav-link>
            </section>

            @if(auth()->user()?->hasAnyRole(['Administrador', 'Vendedor']))
                <section class="space-y-1">
                    <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Operación</p>
                    <x-finance-nav-link :href="route('cash')" :active="request()->routeIs('cash')">
                        <x-slot name="icon"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M16 12h5M7 6V4h10v2"/></svg></x-slot>
                        Caja y arqueo
                    </x-finance-nav-link>
                    <x-finance-nav-link :href="route('invoicing')" :active="request()->routeIs('invoicing')">
                        <x-slot name="icon"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2h9l4 4v16H6z"/><path d="M14 2v5h5M9 12h6M9 16h6"/></svg></x-slot>
                        Nueva facturación
                    </x-finance-nav-link>
                    <x-finance-nav-link :href="route('customers')" :active="request()->routeIs('customers')">
                        <x-slot name="icon"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.87"/></svg></x-slot>
                        Clientes
                    </x-finance-nav-link>
                </section>
            @endif

            @if(auth()->user()?->hasAnyRole(['Administrador', 'Contador']))
                <section class="space-y-1">
                    <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Control contable</p>
                    <x-finance-nav-link :href="route('salesHistory')" :active="request()->routeIs('salesHistory')">
                        <x-slot name="icon"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18M7 16l4-4 3 3 5-7"/></svg></x-slot>
                        Libro de ventas
                    </x-finance-nav-link>
                    <x-finance-nav-link :href="route('inventory')" :active="request()->routeIs('inventory')">
                        <x-slot name="icon"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 8-9-5-9 5 9 5 9-5Z"/><path d="m3 12 9 5 9-5M3 16l9 5 9-5"/></svg></x-slot>
                        Inventario y Kardex
                    </x-finance-nav-link>
                    <x-finance-nav-link :href="route('reports')" :active="request()->routeIs('reports')">
                        <x-slot name="icon"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19V9M10 19V5M16 19v-7M22 19H2"/></svg></x-slot>
                        Informes y exportación
                    </x-finance-nav-link>
                    <x-finance-nav-link :href="route('suppliers')" :active="request()->routeIs('suppliers')">
                        <x-slot name="icon"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h13v10H3zM16 10h3l2 3v4h-5z"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/></svg></x-slot>
                        Proveedores
                    </x-finance-nav-link>
                </section>
            @endif

            <section class="space-y-1">
                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Catálogo</p>
                <x-finance-nav-link :href="route('catalog')" :active="request()->routeIs('catalog')">
                    <x-slot name="icon"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg></x-slot>
                    Consulta de productos
                </x-finance-nav-link>
            </section>

            @if(auth()->user()?->hasRole('Administrador'))
                <section class="space-y-1">
                    <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">Administración</p>
                    <x-finance-nav-link :href="route('users')" :active="request()->routeIs('users')">
                        <x-slot name="icon"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg></x-slot>
                        Usuarios y roles
                    </x-finance-nav-link>
                    <x-finance-nav-link :href="route('configuration')" :active="request()->routeIs('configuration')">
                        <x-slot name="icon"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21h-4v-.1A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3v-4h.1A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3h4v.1A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9c.16.37.4.7.7.96.3.25.68.4 1.1.4h.1v4h-.1c-.42 0-.8.15-1.1.4-.3.26-.54.59-.7.96Z"/></svg></x-slot>
                        Configuración
                    </x-finance-nav-link>
                </section>
            @endif
        </nav>

        <div class="border-t border-white/[0.06] p-4">
            <div class="rounded-xl bg-white/[0.04] p-3">
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-300"><span class="size-2 rounded-full bg-emerald-400 shadow shadow-emerald-400/50"></span>Sistema operativo</div>
                <p class="mt-1 text-[10px] text-slate-500">Ledger y auditoría activos</p>
            </div>
        </div>
    </aside>
</div>
