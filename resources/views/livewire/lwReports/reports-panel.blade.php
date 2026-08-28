<div class="m-2 min-h-[calc(100vh-5rem)] overflow-hidden rounded-lg bg-white shadow-sm">
    @php
        $reports = [
            ['key' => 'sales', 'eyebrow' => 'Ingresos', 'title' => 'Libro de ventas', 'description' => 'Documentos de venta, clientes, fechas, medios de pago e importes contabilizados.', 'pdf' => 'reportSalesPDF', 'excel' => 'reportSalesEXCEL', 'textClass' => 'text-blue-600', 'iconClass' => 'bg-blue-50 text-blue-600'],
            ['key' => 'payments', 'eyebrow' => 'Tesorería', 'title' => 'Relación de pagos', 'description' => 'Cobros registrados y su clasificación por método de liquidación.', 'pdf' => 'reportPaymentsPDF', 'excel' => 'reportPaymentsEXCEL', 'textClass' => 'text-emerald-600', 'iconClass' => 'bg-emerald-50 text-emerald-600'],
            ['key' => 'stock', 'eyebrow' => 'Existencias', 'title' => 'Valuación de stock', 'description' => 'Saldos físicos, mínimos operativos y clasificación de disponibilidad.', 'pdf' => 'reportStockPDF', 'excel' => 'reportStockEXCEL', 'textClass' => 'text-amber-600', 'iconClass' => 'bg-amber-50 text-amber-600'],
            ['key' => 'products', 'eyebrow' => 'Maestro', 'title' => 'Catálogo de productos', 'description' => 'Maestro completo o segmentado por categoría para análisis operativo.', 'pdf' => null, 'excel' => null, 'textClass' => 'text-indigo-600', 'iconClass' => 'bg-indigo-50 text-indigo-600'],
            ['key' => 'customers', 'eyebrow' => 'Cartera', 'title' => 'Directorio de clientes', 'description' => 'Identificación, contacto, ubicación y clasificación comercial.', 'pdf' => 'reportCustomersPDF', 'excel' => 'reportCustomersEXCEL', 'textClass' => 'text-cyan-600', 'iconClass' => 'bg-cyan-50 text-cyan-600'],
            ['key' => 'users', 'eyebrow' => 'Control interno', 'title' => 'Usuarios y accesos', 'description' => 'Relación de usuarios para revisión administrativa y segregación de funciones.', 'pdf' => 'reportUsersPDF', 'excel' => 'reportUsersEXCEL', 'textClass' => 'text-slate-600', 'iconClass' => 'bg-slate-100 text-slate-600'],
        ];
    @endphp

    <div class="mx-auto max-w-screen-2xl space-y-3 p-2">
        <header class="finance-card flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-end sm:justify-between">
            <div><div class="flex items-center gap-2"><span class="rounded-md bg-violet-100 px-2 py-1 text-[10px] font-extrabold uppercase tracking-[0.18em] text-violet-700">Centro documental</span><span class="text-xs font-semibold text-slate-400">PDF · Excel · Trazabilidad</span></div><h1 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-950">Informes y exportación</h1><p class="mt-1 text-sm text-slate-500">Emite soportes financieros y operativos listos para revisión, conciliación o archivo.</p></div>
            <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-extrabold text-emerald-700"><span class="size-2 rounded-full bg-emerald-500"></span>6 familias disponibles</div>
        </header>

        <section class="relative overflow-hidden rounded-md border border-gray-300 bg-gray-950 p-7 text-white shadow-md sm:p-9">
            <div class="absolute -right-24 -top-24 size-80 rounded-full bg-violet-500/15 blur-3xl"></div><div class="absolute -bottom-28 left-1/3 size-64 rounded-full bg-blue-500/10 blur-3xl"></div>
            <div class="relative grid gap-8 lg:grid-cols-[1.3fr_0.7fr] lg:items-end">
                <div><p class="text-[11px] font-extrabold uppercase tracking-[0.2em] text-blue-300">Documentación contable</p><h2 class="mt-3 max-w-2xl text-3xl font-extrabold tracking-tight sm:text-4xl">Información ordenada para decidir, conciliar y rendir cuentas.</h2><p class="mt-4 max-w-2xl text-sm leading-6 text-slate-300">PDF conserva una presentación lista para archivo. Excel entrega datos estructurados para análisis y conciliación externa.</p></div>
                <div class="grid grid-cols-2 gap-3"><div class="rounded-2xl bg-white/[0.06] p-4 ring-1 ring-white/[0.08]"><svg class="size-6 text-rose-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l4 4v16H6zM14 2v5h5M9 13h6M9 17h4"/></svg><p class="mt-4 text-sm font-extrabold">PDF</p><p class="mt-1 text-[11px] text-slate-400">Archivo y presentación</p></div><div class="rounded-2xl bg-white/[0.06] p-4 ring-1 ring-white/[0.08]"><svg class="size-6 text-emerald-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l4 4v16H6zM14 2v5h5M9 13h6M9 17h6M12 10v10"/></svg><p class="mt-4 text-sm font-extrabold">Excel</p><p class="mt-1 text-[11px] text-slate-400">Análisis y conciliación</p></div></div>
            </div>
        </section>

        <section>
            <div class="mb-4 flex items-center justify-between"><div><p class="finance-label">Biblioteca</p><h2 class="mt-1 text-lg font-extrabold text-slate-900">Familias de informes</h2></div><p class="hidden text-xs font-semibold text-slate-400 sm:block">Selecciona el formato de salida</p></div>
            <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
                @foreach($reports as $report)
                    <article class="finance-card group overflow-hidden transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div><p class="text-[10px] font-extrabold uppercase tracking-[0.16em] {{ $report['textClass'] }}">{{ $report['eyebrow'] }}</p><h3 class="mt-2 text-lg font-extrabold text-slate-900">{{ $report['title'] }}</h3></div>
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $report['iconClass'] }}">
                                    @if($report['key'] === 'sales')<svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18M7 15l4-4 3 3 5-7"/></svg>
                                    @elseif($report['key'] === 'payments')<svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/></svg>
                                    @elseif($report['key'] === 'stock' || $report['key'] === 'products')<svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 8-9-5-9 5 9 5 9-5Z"/><path d="m3 12 9 5 9-5"/></svg>
                                    @elseif($report['key'] === 'customers')<svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0M16 11h6M19 8v6"/></svg>
                                    @else<svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>@endif
                                </span>
                            </div>
                            <p class="mt-4 min-h-10 text-sm leading-5 text-slate-500">{{ $report['description'] }}</p>
                        </div>
                        <div class="grid grid-cols-2 border-t border-slate-100 bg-slate-50/60">
                            @if($report['key'] === 'products')
                                <button type="button" wire:click="$dispatch('open-modal', 'modal-form-paramProductsPDF')" class="flex items-center justify-center gap-2 border-r border-slate-100 px-4 py-3 text-xs font-extrabold text-rose-700 transition hover:bg-rose-50"><span class="rounded bg-rose-100 px-1.5 py-0.5 text-[9px]">PDF</span>Generar</button>
                                <button type="button" wire:click="$dispatch('open-modal', 'modal-form-paramProductsEXCEL')" class="flex items-center justify-center gap-2 px-4 py-3 text-xs font-extrabold text-emerald-700 transition hover:bg-emerald-50"><span class="rounded bg-emerald-100 px-1.5 py-0.5 text-[9px]">XLSX</span>Exportar</button>
                            @else
                                <button type="button" wire:click="{{ $report['pdf'] }}" wire:loading.attr="disabled" class="flex items-center justify-center gap-2 border-r border-slate-100 px-4 py-3 text-xs font-extrabold text-rose-700 transition hover:bg-rose-50"><span class="rounded bg-rose-100 px-1.5 py-0.5 text-[9px]">PDF</span>Generar</button>
                                <button type="button" wire:click="{{ $report['excel'] }}" wire:loading.attr="disabled" class="flex items-center justify-center gap-2 px-4 py-3 text-xs font-extrabold text-emerald-700 transition hover:bg-emerald-50"><span class="rounded bg-emerald-100 px-1.5 py-0.5 text-[9px]">XLSX</span>Exportar</button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-3">
            <article class="finance-card p-5"><div class="flex items-center gap-3"><span class="flex size-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg></span><div><h3 class="text-sm font-extrabold text-slate-900">Descarga directa</h3><p class="text-xs text-slate-400">El archivo se genera al momento.</p></div></div></article>
            <article class="finance-card p-5"><div class="flex items-center gap-3"><span class="flex size-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg></span><div><h3 class="text-sm font-extrabold text-slate-900">Datos vigentes</h3><p class="text-xs text-slate-400">Consulta el ledger al emitir.</p></div></div></article>
            <article class="finance-card p-5"><div class="flex items-center gap-3"><span class="flex size-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/></svg></span><div><h3 class="text-sm font-extrabold text-slate-900">Corte actual</h3><p class="text-xs text-slate-400">{{ now()->translatedFormat('d M Y · H:i') }}</p></div></div></article>
        </section>
    </div>

    @foreach(['EXCEL' => 'Excel', 'PDF' => 'PDF'] as $formatKey => $formatLabel)
        <form wire:submit="reportProducts{{ $formatKey }}">
            <x-dialog-modal name="modal-form-paramProducts{{ $formatKey }}" maxWidth="md">
                <x-slot name="title"><div><p class="finance-label">Parámetros del informe</p><h2 class="mt-1 text-xl font-extrabold text-slate-900">Catálogo de productos · {{ $formatLabel }}</h2></div></x-slot>
                <x-slot name="content"><label class="block"><span class="finance-label">Categoría</span><select class="finance-input mt-2" wire:model="selectedCategory"><option value="0">Todas las categorías</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></label>@error('selectedCategory')<p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>@enderror<div class="mt-4 rounded-xl bg-slate-50 p-3 text-xs leading-5 text-slate-500">El archivo incluirá el catálogo activo según la clasificación seleccionada.</div></x-slot>
                <x-slot name="footer"><button type="button" wire:click="$dispatch('close-modal', 'modal-form-paramProducts{{ $formatKey }}')" class="rounded-xl border border-slate-300 px-4 py-2.5 text-xs font-extrabold text-slate-700">Cancelar</button><button type="submit" class="ms-3 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-extrabold text-white hover:bg-blue-700">Generar {{ $formatLabel }}</button></x-slot>
            </x-dialog-modal>
        </form>
    @endforeach
</div>
