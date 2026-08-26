<div class="leo-page">
    <div class="leo-container max-w-7xl">
        <header class="leo-header">
            <div>
                <h1 class="leo-title">Resumen del negocio</h1>
                <p class="leo-subtitle">Indicadores y actividad reciente de tu empresa.</p>
            </div>
            <a href="{{ route('invoicing') }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Registrar nueva venta</a>
        </header>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="absolute inset-x-0 top-0 h-1 bg-blue-500"></div>
                <p class="text-sm font-semibold text-slate-500">Ventas realizadas</p>
                <p class="mt-3 text-4xl font-bold tracking-tight text-slate-900">{{ number_format($totalSales) }}</p>
                <p class="mt-2 text-xs font-semibold text-blue-600">Acumulado histórico</p>
            </article>
            <a href="{{ route('customers') }}" class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="absolute inset-x-0 top-0 h-1 bg-emerald-500"></div>
                <p class="text-sm font-semibold text-slate-500">Clientes registrados</p>
                <p class="mt-3 text-4xl font-bold tracking-tight text-slate-900">{{ number_format($totalCustomers) }}</p>
                <p class="mt-2 text-xs font-semibold text-emerald-600">Ver clientes →</p>
            </a>
            <a href="{{ route('inventory') }}" class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:col-span-2 xl:col-span-1">
                <div class="absolute inset-x-0 top-0 h-1 bg-amber-500"></div>
                <p class="text-sm font-semibold text-slate-500">Productos disponibles</p>
                <p class="mt-3 text-4xl font-bold tracking-tight text-slate-900">{{ number_format($totalProducts) }}</p>
                <p class="mt-2 text-xs font-semibold text-amber-600">Revisar inventario →</p>
            </a>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
                <h2 class="font-bold text-slate-900">Ventas por mes</h2>
                <p class="text-sm text-slate-500">Ingresos registrados durante el año</p>
                @php
                    $monthNames = [1 => 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                    $maxMonthly = max($ventasPorMes ?: [1]);
                @endphp
                <div class="mt-6 flex h-[250px] items-end gap-2 rounded-xl bg-slate-50 px-4 pb-10 pt-5" aria-label="Gráfico de ventas por mes">
                    @foreach(range(1, 12) as $month)
                        @php
                            $value = (float) ($ventasPorMes[$month] ?? 0);
                            $height = $value > 0 ? max(8, ($value / $maxMonthly) * 100) : 2;
                        @endphp
                        <div class="group relative flex h-full min-w-0 flex-1 items-end">
                            <div class="w-full rounded-t-md bg-blue-600 transition hover:bg-blue-700" style="height: {{ $height }}%" title="{{ $monthNames[$month] }}: ${{ number_format($value, 2) }}">
                                <span class="pointer-events-none absolute bottom-[calc(100%+8px)] left-1/2 z-10 hidden -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-xs font-semibold text-white shadow group-hover:block">${{ number_format($value, 2) }}</span>
                            </div>
                            <span class="absolute -bottom-7 left-1/2 -translate-x-1/2 text-[10px] font-semibold text-slate-500 sm:text-xs">{{ $monthNames[$month] }}</span>
                        </div>
                    @endforeach
                </div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-bold text-slate-900">Métodos de pago</h2>
                <p class="text-sm text-slate-500">Distribución de las transacciones</p>
                <div class="flex min-h-[310px] flex-col items-center justify-center" aria-label="Gráfico de métodos de pago">
                    <div class="relative h-48 w-48 rounded-full shadow-inner" style="background: conic-gradient(#10b981 0% {{ $paymentComparison['cash'] }}%, #2563eb {{ $paymentComparison['cash'] }}% 100%);">
                        <div class="absolute inset-7 flex flex-col items-center justify-center rounded-full bg-white shadow-sm">
                            <span class="text-2xl font-bold text-slate-900">100%</span>
                            <span class="text-xs text-slate-500">de las ventas</span>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-wrap justify-center gap-4 text-sm">
                        <div class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-emerald-500"></span><span class="text-slate-600">Efectivo</span><strong>{{ $paymentComparison['cash'] }}%</strong></div>
                        <div class="flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-blue-600"></span><span class="text-slate-600">PayPal</span><strong>{{ $paymentComparison['paypal'] }}%</strong></div>
                    </div>
                </div>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-5">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
                <h2 class="font-bold text-slate-900">Vendedores destacados</h2>
                <p class="text-sm text-slate-500">Clasificación por ventas completadas</p>
                @php($maxSellerSales = max($topSellers->pluck('sales_count')->toArray() ?: [1]))
                <div class="mt-7 space-y-6" aria-label="Gráfico de vendedores destacados">
                    @forelse($topSellers as $index => $seller)
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                                <span class="truncate font-semibold text-slate-700">{{ $index + 1 }}. {{ $seller->user?->name ?? 'Usuario' }}</span>
                                <span class="whitespace-nowrap font-bold text-slate-900">{{ $seller->sales_count }} ventas</span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-slate-800 transition-all duration-500" style="width: {{ max(5, ($seller->sales_count / $maxSellerSales) * 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="py-16 text-center text-sm text-slate-500">No hay ventas registradas.</p>
                    @endforelse
                </div>
            </article>
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-3">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-bold text-slate-900">Ventas recientes</h2>
                    <p class="text-sm text-slate-500">Últimos movimientos registrados</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr><th class="px-5 py-3">Venta</th><th class="px-5 py-3">Cliente</th><th class="px-5 py-3">Fecha</th><th class="px-5 py-3 text-right">Total</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentSales as $sale)
                                <tr class="text-slate-700 transition hover:bg-slate-50">
                                    <td class="px-5 py-4 font-bold text-slate-900">#{{ $sale->id }}</td>
                                    <td class="px-5 py-4">{{ $sale->customer?->name ?? 'Sin cliente' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-500">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</td>
                                    <td class="px-5 py-4 text-right font-bold text-blue-600">${{ number_format($sale->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-10 text-center text-slate-500">Aún no hay ventas registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        <nav class="flex flex-wrap justify-center gap-3" aria-label="Accesos rápidos">
            <a href="{{ route('invoicing') }}" class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">Nueva venta</a>
            <a href="{{ route('customers') }}" class="rounded-xl bg-emerald-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">Ver clientes</a>
            <a href="{{ route('inventory') }}" class="rounded-xl bg-slate-800 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-slate-900">Ver productos</a>
        </nav>
    </div>
</div>
