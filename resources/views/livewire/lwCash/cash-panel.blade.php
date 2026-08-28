<div class="m-2 min-h-[calc(100vh-5rem)] overflow-hidden rounded-lg bg-white shadow-sm">
    <div class="mx-auto max-w-screen-2xl space-y-3 p-2">
        <header class="finance-card flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded-md bg-blue-100 px-2 py-1 text-[10px] font-extrabold uppercase tracking-[0.18em] text-blue-700">Tesorería operativa</span>
                    <span class="text-xs font-semibold text-slate-400">{{ now()->translatedFormat('d M Y · H:i') }}</span>
                </div>
                <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-950">Caja y arqueo</h1>
                <p class="mt-1 max-w-2xl text-sm text-slate-500">Control del efectivo, ingresos, egresos y conciliación del turno en curso.</p>
            </div>
            <div @class([
                'inline-flex w-fit items-center gap-2 rounded-full border px-4 py-2 text-xs font-extrabold',
                'border-emerald-200 bg-emerald-50 text-emerald-700' => $session,
                'border-slate-200 bg-white text-slate-500' => ! $session,
            ])>
                <span @class(['size-2 rounded-full', 'bg-emerald-500 shadow shadow-emerald-400' => $session, 'bg-slate-300' => ! $session])></span>
                {{ $session ? 'Turno abierto' : 'Sin turno activo' }}
            </div>
        </header>

        @if (!$session)
            <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                <div class="relative overflow-hidden rounded-md border border-gray-300 bg-gray-950 p-7 text-white shadow-md sm:p-10">
                    <div class="absolute -right-20 -top-20 size-72 rounded-full bg-blue-500/20 blur-3xl"></div>
                    <div class="absolute -bottom-28 left-1/4 size-64 rounded-full bg-emerald-400/10 blur-3xl"></div>
                    <div class="relative max-w-xl">
                        <div class="flex size-12 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/10">
                            <svg class="size-6 text-blue-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M16 12h5M7 6V4h10v2"/></svg>
                        </div>
                        <p class="mt-8 text-xs font-extrabold uppercase tracking-[0.2em] text-blue-300">Inicio de jornada</p>
                        <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Abre un turno de caja para comenzar a operar.</h2>
                        <p class="mt-4 text-sm leading-6 text-slate-300">El fondo inicial será la base del arqueo. Ventas, ingresos, retiros y reembolsos quedarán vinculados a esta sesión.</p>
                        <div class="mt-8 grid grid-cols-3 gap-3 text-center">
                            <div class="rounded-xl bg-white/[0.06] p-3 ring-1 ring-white/[0.08]"><p class="text-lg font-black">01</p><p class="mt-1 text-[10px] uppercase tracking-wider text-slate-400">Apertura</p></div>
                            <div class="rounded-xl bg-white/[0.06] p-3 ring-1 ring-white/[0.08]"><p class="text-lg font-black">02</p><p class="mt-1 text-[10px] uppercase tracking-wider text-slate-400">Operación</p></div>
                            <div class="rounded-xl bg-white/[0.06] p-3 ring-1 ring-white/[0.08]"><p class="text-lg font-black">03</p><p class="mt-1 text-[10px] uppercase tracking-wider text-slate-400">Arqueo</p></div>
                        </div>
                    </div>
                </div>

                <form wire:submit="openSession" class="finance-card p-6 sm:p-8">
                    <div class="flex items-start justify-between gap-4">
                        <div><p class="finance-label">Nuevo turno</p><h2 class="mt-1 text-xl font-extrabold text-slate-900">Apertura de caja</h2></div>
                        <span class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span>
                    </div>
                    <label class="mt-7 block">
                        <span class="finance-label">Fondo inicial</span>
                        <div class="relative mt-2"><span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-bold text-slate-400">C$</span><input wire:model="openingAmount" type="number" min="0" step="0.01" class="finance-input finance-money py-3 pl-12 text-lg font-extrabold" placeholder="0.00"></div>
                    </label>
                    @error('openingAmount') <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                    <label class="mt-5 block"><span class="finance-label">Nota de apertura</span><textarea wire:model="openingNotes" rows="3" class="finance-input mt-2" placeholder="Origen del fondo o referencia del turno"></textarea></label>
                    @error('openingNotes') <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                    <button class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-blue-200 transition hover:bg-blue-700" wire:loading.attr="disabled">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        Abrir turno de caja
                    </button>
                </form>
            </section>
        @else
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="finance-card p-5"><div class="flex items-start justify-between"><div><p class="finance-label">Fondo inicial</p><p class="finance-money mt-3 text-2xl font-extrabold text-slate-950">C$ {{ number_format($session->opening_amount, 2) }}</p></div><span class="rounded-lg bg-slate-100 p-2 text-slate-500"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18v13H3zM16 12h5"/></svg></span></div><p class="mt-3 text-xs text-slate-400">{{ $session->register->name }} · {{ $session->opened_at->format('H:i') }}</p></article>
                <article class="finance-card p-5"><div class="flex items-start justify-between"><div><p class="finance-label">Entradas del turno</p><p class="finance-money mt-3 text-2xl font-extrabold text-emerald-700">+ C$ {{ number_format($income, 2) }}</p></div><span class="rounded-lg bg-emerald-50 p-2 text-emerald-600"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7 14 5-5 5 5M12 9v10"/></svg></span></div><p class="mt-3 text-xs text-slate-400">Ventas e ingresos extraordinarios</p></article>
                <article class="finance-card p-5"><div class="flex items-start justify-between"><div><p class="finance-label">Salidas del turno</p><p class="finance-money mt-3 text-2xl font-extrabold text-rose-700">− C$ {{ number_format($outflow, 2) }}</p></div><span class="rounded-lg bg-rose-50 p-2 text-rose-600"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7 10 5 5 5-5M12 15V5"/></svg></span></div><p class="mt-3 text-xs text-slate-400">Gastos, retiros y reembolsos</p></article>
                <article class="rounded-2xl bg-[#14213d] p-5 text-white shadow-lg shadow-slate-300"><div class="flex items-start justify-between"><div><p class="text-[11px] font-bold uppercase tracking-[0.14em] text-blue-300">Saldo esperado</p><p class="finance-money mt-3 text-2xl font-extrabold">C$ {{ number_format($expectedAmount, 2) }}</p></div><span class="rounded-lg bg-white/10 p-2 text-blue-200"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span></div><p class="mt-3 text-xs text-slate-400">Saldo teórico para conciliación</p></article>
            </section>

            <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                <form wire:submit="addMovement" class="finance-card p-6">
                    <div class="flex items-center justify-between"><div><p class="finance-label">Asiento de caja</p><h2 class="mt-1 text-lg font-extrabold text-slate-900">Registrar movimiento manual</h2></div><span class="rounded-full bg-amber-50 px-3 py-1 text-[10px] font-extrabold uppercase tracking-wider text-amber-700">Requiere motivo</span></div>
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <label><span class="finance-label">Naturaleza</span><select wire:model="movementType" class="finance-input mt-2 py-3"><option value="income">Ingreso extraordinario</option><option value="expense">Gasto operativo</option><option value="withdrawal">Retiro de efectivo</option></select></label>
                        <label><span class="finance-label">Importe</span><div class="relative mt-2"><span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-bold text-slate-400">C$</span><input wire:model="movementAmount" type="number" min="0.01" step="0.01" class="finance-input finance-money py-3 pl-12 font-bold" placeholder="0.00"></div></label>
                    </div>
                    <label class="mt-4 block"><span class="finance-label">Concepto / justificación</span><input wire:model="movementReason" type="text" class="finance-input mt-2 py-3" placeholder="Ej. Reposición de fondo menor"></label>
                    @error('movementAmount') <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                    @error('movementReason') <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                    <button class="mt-5 inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-slate-800"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>Registrar asiento</button>
                </form>

                <form wire:submit="closeSession" class="overflow-hidden rounded-2xl border border-amber-200 bg-amber-50/70 shadow-sm">
                    <div class="border-b border-amber-200 bg-white/70 px-6 py-5"><div class="flex items-center justify-between"><div><p class="text-[11px] font-bold uppercase tracking-[0.14em] text-amber-700">Conciliación</p><h2 class="mt-1 text-lg font-extrabold text-slate-900">Cerrar y arquear turno</h2></div><span class="rounded-lg bg-amber-100 p-2 text-amber-700"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2Z"/></svg></span></div></div>
                    <div class="p-6">
                        <div class="rounded-xl border border-amber-200 bg-white p-4"><div class="flex items-center justify-between text-sm"><span class="font-semibold text-slate-500">Saldo esperado por sistema</span><strong class="finance-money text-lg text-slate-900">C$ {{ number_format($expectedAmount, 2) }}</strong></div></div>
                        <label class="mt-4 block"><span class="finance-label">Efectivo contado físicamente</span><div class="relative mt-2"><span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-bold text-slate-400">C$</span><input wire:model="closingAmount" type="number" min="0" step="0.01" class="finance-input finance-money py-3 pl-12 text-lg font-extrabold" placeholder="0.00"></div></label>
                        <label class="mt-4 block"><span class="finance-label">Observación del arqueo</span><textarea wire:model="closingNotes" rows="2" class="finance-input mt-2" placeholder="Explique cualquier diferencia o incidencia"></textarea></label>
                        @error('closingAmount') <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                        <button class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-amber-600 px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-amber-700" wire:loading.attr="disabled"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>Confirmar cierre de caja</button>
                    </div>
                </form>
            </section>

            <section class="finance-card overflow-hidden">
                <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"><div><p class="finance-label">Mayor auxiliar</p><h2 class="mt-1 text-lg font-extrabold text-slate-900">Movimientos del turno</h2></div><span class="text-xs font-semibold text-slate-400">Últimos {{ $movements->count() }} registros</span></div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500"><tr><th class="px-5 py-3 text-left">Hora</th><th class="px-5 py-3 text-left">Tipo</th><th class="px-5 py-3 text-left">Concepto</th><th class="px-5 py-3 text-left">Referencia</th><th class="px-5 py-3 text-right">Debe / Haber</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($movements as $movement)
                                @php($isInflow = in_array($movement->type, ['sale', 'income'], true))
                                <tr class="transition hover:bg-slate-50/80"><td class="whitespace-nowrap px-5 py-4 text-xs font-semibold text-slate-400">{{ $movement->created_at->format('H:i:s') }}</td><td class="px-5 py-4"><span @class(['inline-flex rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider', 'bg-emerald-50 text-emerald-700' => $isInflow, 'bg-rose-50 text-rose-700' => ! $isInflow])>{{ match($movement->type) { 'sale' => 'Venta', 'income' => 'Ingreso', 'expense' => 'Gasto', 'withdrawal' => 'Retiro', 'refund' => 'Reembolso', default => $movement->type } }}</span></td><td class="max-w-md px-5 py-4 font-semibold text-slate-700">{{ $movement->reason }}</td><td class="px-5 py-4 text-xs text-slate-400">{{ $movement->reference_type ? class_basename($movement->reference_type).' #'.$movement->reference_id : 'Manual' }}</td><td @class(['finance-money whitespace-nowrap px-5 py-4 text-right font-extrabold', 'text-emerald-700' => $isInflow, 'text-rose-700' => ! $isInflow])>{{ $isInflow ? '+' : '−' }} C$ {{ number_format($movement->amount, 2) }}</td></tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-14 text-center"><div class="mx-auto flex size-11 items-center justify-center rounded-full bg-slate-100 text-slate-400"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16v12H4zM8 10h8M8 14h5"/></svg></div><p class="mt-3 font-semibold text-slate-500">Sin movimientos registrados</p></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if($recentSessions->isNotEmpty())
            <section class="finance-card overflow-hidden">
                <div class="border-b border-slate-200 px-5 py-4"><p class="finance-label">Trazabilidad</p><h2 class="mt-1 text-lg font-extrabold text-slate-900">Cierres recientes</h2></div>
                <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500"><tr><th class="px-5 py-3 text-left">Turno</th><th class="px-5 py-3 text-left">Apertura</th><th class="px-5 py-3 text-right">Esperado</th><th class="px-5 py-3 text-right">Contado</th><th class="px-5 py-3 text-right">Diferencia</th><th class="px-5 py-3 text-center">Estado</th></tr></thead><tbody class="divide-y divide-slate-100">@foreach($recentSessions as $item)<tr><td class="px-5 py-4 font-bold text-slate-800">#{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }} · {{ $item->register?->name }}</td><td class="px-5 py-4 text-slate-500">{{ $item->opened_at?->format('d/m/Y H:i') }}</td><td class="finance-money px-5 py-4 text-right font-semibold">{{ $item->expected_amount !== null ? 'C$ '.number_format($item->expected_amount, 2) : '—' }}</td><td class="finance-money px-5 py-4 text-right font-semibold">{{ $item->closing_amount !== null ? 'C$ '.number_format($item->closing_amount, 2) : '—' }}</td><td @class(['finance-money px-5 py-4 text-right font-extrabold', 'text-emerald-700' => (float) $item->difference === 0.0, 'text-rose-700' => (float) $item->difference !== 0.0])>{{ $item->difference !== null ? 'C$ '.number_format($item->difference, 2) : '—' }}</td><td class="px-5 py-4 text-center"><span @class(['rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase', 'bg-emerald-50 text-emerald-700' => $item->status === 'open', 'bg-slate-100 text-slate-600' => $item->status === 'closed'])>{{ $item->status === 'open' ? 'Abierta' : 'Cerrada' }}</span></td></tr>@endforeach</tbody></table></div>
            </section>
        @endif
    </div>
</div>
