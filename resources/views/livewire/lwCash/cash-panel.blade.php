<div class="min-h-[calc(100vh-4rem)] bg-slate-50 p-6">
    <div class="mx-auto max-w-6xl space-y-6">
        <header>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Control operativo</p>
            <h1 class="text-3xl font-bold text-slate-900">Caja</h1>
            <p class="text-sm text-slate-500">Apertura, movimientos y arqueo del turno actual.</p>
        </header>

        @if (!$session)
            <form wire:submit="openSession" class="max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Abrir caja</h2>
                <label class="mt-5 block text-sm font-semibold text-slate-700">Fondo inicial</label>
                <input wire:model="openingAmount" type="number" min="0" step="0.01" class="mt-1 w-full rounded-xl border-slate-300">
                @error('openingAmount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <label class="mt-4 block text-sm font-semibold text-slate-700">Observaciones</label>
                <textarea wire:model="openingNotes" class="mt-1 w-full rounded-xl border-slate-300"></textarea>
                <button class="mt-5 rounded-xl bg-blue-600 px-5 py-3 font-bold text-white hover:bg-blue-700">Abrir turno</button>
            </form>
        @else
            <section class="grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Caja</p><p class="mt-2 text-xl font-bold">{{ $session->register->name }}</p></div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm text-slate-500">Fondo inicial</p><p class="mt-2 text-xl font-bold">${{ number_format($session->opening_amount, 2) }}</p></div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5"><p class="text-sm text-emerald-700">Estado</p><p class="mt-2 text-xl font-bold text-emerald-800">Caja abierta</p></div>
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <form wire:submit="addMovement" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold">Registrar movimiento</h2>
                    <select wire:model="movementType" class="mt-4 w-full rounded-xl border-slate-300"><option value="income">Ingreso</option><option value="expense">Gasto</option><option value="withdrawal">Retiro</option></select>
                    <input wire:model="movementAmount" type="number" min="0.01" step="0.01" placeholder="Monto" class="mt-3 w-full rounded-xl border-slate-300">
                    <input wire:model="movementReason" type="text" placeholder="Motivo" class="mt-3 w-full rounded-xl border-slate-300">
                    @error('movementAmount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @error('movementReason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <button class="mt-4 rounded-xl bg-slate-800 px-5 py-3 font-bold text-white">Guardar movimiento</button>
                </form>

                <form wire:submit="closeSession" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold">Cerrar y arquear</h2>
                    <input wire:model="closingAmount" type="number" min="0" step="0.01" placeholder="Efectivo contado" class="mt-4 w-full rounded-xl border-slate-300">
                    <textarea wire:model="closingNotes" placeholder="Observaciones del cierre" class="mt-3 w-full rounded-xl border-slate-300"></textarea>
                    @error('closingAmount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <button class="mt-4 rounded-xl bg-red-600 px-5 py-3 font-bold text-white">Cerrar caja</button>
                </form>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b px-5 py-4"><h2 class="font-bold">Movimientos recientes</h2></div>
                <table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="px-5 py-3 text-left">Tipo</th><th class="px-5 py-3 text-left">Motivo</th><th class="px-5 py-3 text-right">Monto</th></tr></thead><tbody class="divide-y">
                @forelse($movements as $movement)<tr><td class="px-5 py-3 capitalize">{{ $movement->type }}</td><td class="px-5 py-3">{{ $movement->reason }}</td><td class="px-5 py-3 text-right font-bold">${{ number_format($movement->amount, 2) }}</td></tr>@empty<tr><td colspan="3" class="px-5 py-8 text-center text-slate-500">Sin movimientos.</td></tr>@endforelse
                </tbody></table>
            </section>
        @endif
    </div>
</div>
