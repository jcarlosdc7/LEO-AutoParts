<div class="m-2 min-h-[calc(100vh-5rem)] overflow-hidden rounded-lg bg-white shadow-sm">
    <div class="space-y-3 p-2">
        <header class="flex flex-col gap-4 rounded-md border border-gray-300 bg-gray-100 px-5 py-4 shadow-md lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Tesorería operativa</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900">Caja y arqueo</h1>
                <p class="mt-1 text-sm text-gray-600">Apertura, movimientos, conteo físico y conciliación del turno.</p>
            </div>
            <label class="w-full lg:w-72">
                <span class="text-xs font-semibold text-gray-700">Caja de trabajo</span>
                <select wire:model.live="registerCode" class="mt-1 w-full rounded-md border-gray-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($session)>
                    @foreach($registers as $register)
                        <option value="{{ $register->code }}">{{ $register->name }} · {{ $register->code }}</option>
                    @endforeach
                </select>
            </label>
        </header>

        @error('cash')<div class="rounded-md border border-red-300 bg-red-50 p-3 text-sm font-semibold text-red-700">{{ $message }}</div>@enderror
        @error('registerCode')<div class="rounded-md border border-red-300 bg-red-50 p-3 text-sm font-semibold text-red-700">{{ $message }}</div>@enderror

        @if(!$session)
            <section class="rounded-md border-2 border-gray-900 bg-white shadow-md">
                <div class="grid gap-6 p-6 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div class="flex items-start gap-4">
                        <span class="flex size-14 shrink-0 items-center justify-center rounded-md bg-gray-900 text-white">
                            <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M16 12h5M7 6V4h10v2"/></svg>
                        </span>
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full bg-red-100 px-3 py-1 text-xs font-extrabold text-red-700"><span class="size-2 rounded-full bg-red-600"></span>CAJA CERRADA</div>
                            <h2 class="mt-3 text-2xl font-bold text-gray-900">No existe una sesión activa en esta caja.</h2>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600">Cuenta el fondo físico por billetes y monedas antes de registrar ventas o movimientos.</p>
                        </div>
                    </div>
                    <button type="button" wire:click="showOpening" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-md bg-green-600 px-7 py-3 text-sm font-bold text-white shadow hover:bg-green-700">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                        ABRIR CAJA
                    </button>
                </div>
            </section>
        @else
            <section class="rounded-md border border-green-300 bg-green-50 shadow-md">
                <div class="grid gap-5 p-5 xl:grid-cols-[1.2fr_1fr_auto] xl:items-center">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-green-600 px-3 py-1 text-xs font-extrabold text-white"><span class="size-2 rounded-full bg-white"></span>CAJA ABIERTA</div>
                        <h2 class="mt-3 text-xl font-bold text-gray-900">{{ $session->register->name }}</h2>
                        <p class="mt-1 text-sm text-gray-600">Operador: <strong>{{ $session->user->name }}</strong> · Apertura: {{ $session->opened_at->timezone(config('cash.timezone'))->format('d/m/Y H:i') }}</p>
                    </div>
                    <dl class="grid grid-cols-2 gap-3">
                        <div class="rounded-md border border-green-200 bg-white p-3"><dt class="text-xs font-semibold text-gray-500">Fondo inicial</dt><dd class="mt-1 text-lg font-black text-gray-900">{{ $currencySymbol }} {{ number_format($session->opening_amount, 2) }}</dd></div>
                        <div class="rounded-md border border-green-200 bg-white p-3"><dt class="text-xs font-semibold text-gray-500">Efectivo esperado</dt><dd class="mt-1 text-lg font-black text-gray-900">{{ $currencySymbol }} {{ number_format($breakdown['expected'], 2) }}</dd></div>
                    </dl>
                    <div class="flex flex-col gap-2 sm:flex-row xl:flex-col">
                        <button type="button" wire:click="showMovement" class="rounded-md bg-gray-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-gray-800">Registrar movimiento</button>
                        <button type="button" wire:click="showClosing" class="rounded-md bg-amber-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-amber-700">Cerrar caja</button>
                    </div>
                </div>
            </section>

            <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach([
                    ['Fondo inicial', $session->opening_amount, 'text-gray-900'],
                    ['Ventas efectivo', $breakdown['sale'] ?? '0.00', 'text-green-700'],
                    ['Ingresos y depósitos', bcadd($breakdown['income'] ?? '0.00', $breakdown['deposit'] ?? '0.00', 2), 'text-green-700'],
                    ['Refunds / gastos / retiros', bcadd(bcadd($breakdown['refund'] ?? '0.00', $breakdown['expense'] ?? '0.00', 2), $breakdown['withdrawal'] ?? '0.00', 2), 'text-red-700'],
                ] as [$label, $value, $color])
                    <article class="rounded-md border border-gray-300 bg-gray-100 p-4 shadow-md"><p class="text-xs font-semibold text-gray-600">{{ $label }}</p><p class="mt-2 text-xl font-black {{ $color }}">{{ $currencySymbol }} {{ number_format($value, 2) }}</p></article>
                @endforeach
            </section>

            <section class="overflow-hidden rounded-md border border-gray-300 bg-gray-100 shadow-md">
                <div class="flex items-center justify-between border-b border-gray-300 px-5 py-4"><div><p class="text-xs font-semibold text-gray-500">Mayor auxiliar</p><h2 class="text-lg font-bold text-gray-900">Movimientos del turno</h2></div><span class="text-xs text-gray-500">Paginación server-side</span></div>
                <div class="overflow-x-auto bg-white">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-900 text-xs uppercase text-white"><tr><th class="px-4 py-3 text-left">Fecha/hora</th><th class="px-4 py-3 text-left">Tipo</th><th class="px-4 py-3 text-left">Motivo</th><th class="px-4 py-3 text-left">Referencia</th><th class="px-4 py-3 text-right">Monto</th></tr></thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($movements as $movement)
                                @php($inflow = in_array($movement->type, \App\Services\CashService::INFLOW_TYPES, true))
                                <tr><td class="whitespace-nowrap px-4 py-3 text-gray-500">{{ $movement->created_at->timezone(config('cash.timezone'))->format('d/m/Y H:i:s') }}</td><td class="px-4 py-3 font-bold">{{ strtoupper($movement->type) }}</td><td class="px-4 py-3 text-gray-700">{{ $movement->reason }}</td><td class="px-4 py-3 text-gray-500">{{ $movement->reference ?: ($movement->reference_type ? class_basename($movement->reference_type).' #'.$movement->reference_id : 'Manual') }}</td><td class="whitespace-nowrap px-4 py-3 text-right font-black {{ $inflow ? 'text-green-700' : 'text-red-700' }}">{{ $inflow ? '+' : '−' }} {{ $currencySymbol }} {{ number_format($movement->amount, 2) }}</td></tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-10 text-center text-gray-500">No hay movimientos en el turno.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-300 bg-white px-4 py-3">{{ $movements->links() }}</div>
            </section>
        @endif

        <section class="overflow-hidden rounded-md border border-gray-300 bg-gray-100 shadow-md">
            <div class="border-b border-gray-300 px-5 py-4"><p class="text-xs font-semibold text-gray-500">Trazabilidad</p><h2 class="text-lg font-bold text-gray-900">Historial de cajas</h2></div>
            <div class="grid gap-3 border-b border-gray-300 bg-white p-4 sm:grid-cols-2 xl:grid-cols-6">
                <label><span class="text-xs font-semibold text-gray-700">Desde</span><input type="date" wire:model.live="historyFrom" class="mt-1 w-full rounded-md border-gray-300 text-sm"></label>
                <label><span class="text-xs font-semibold text-gray-700">Hasta</span><input type="date" wire:model.live="historyTo" class="mt-1 w-full rounded-md border-gray-300 text-sm"></label>
                <label><span class="text-xs font-semibold text-gray-700">Estado</span><select wire:model.live="historyStatus" class="mt-1 w-full rounded-md border-gray-300 text-sm"><option value="all">Todos</option><option value="open">Abiertas</option><option value="closed">Cerradas</option></select></label>
                <label><span class="text-xs font-semibold text-gray-700">Diferencia</span><select wire:model.live="historyDifference" class="mt-1 w-full rounded-md border-gray-300 text-sm"><option value="all">Todas</option><option value="with">Con diferencia</option><option value="without">Sin diferencia</option></select></label>
                <label><span class="text-xs font-semibold text-gray-700">Caja</span><select wire:model.live="historyRegister" class="mt-1 w-full rounded-md border-gray-300 text-sm"><option value="all">Todas</option>@foreach($registers as $register)<option value="{{ $register->id }}">{{ $register->name }}</option>@endforeach</select></label>
                <label><span class="text-xs font-semibold text-gray-700">Operador</span><select wire:model.live="historyUser" class="mt-1 w-full rounded-md border-gray-300 text-sm"><option value="all">Todos</option>@foreach($historyUsers as $operator)<option value="{{ $operator->id }}">{{ $operator->name }}</option>@endforeach</select></label>
            </div>
            <div class="overflow-x-auto bg-white">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-900 text-xs uppercase text-white"><tr><th class="px-4 py-3 text-left">Sesión</th><th class="px-4 py-3 text-left">Caja / operador</th><th class="px-4 py-3 text-left">Apertura / cierre</th><th class="px-4 py-3 text-right">Inicial</th><th class="px-4 py-3 text-right">Esperado</th><th class="px-4 py-3 text-right">Contado</th><th class="px-4 py-3 text-right">Diferencia</th><th class="px-4 py-3"></th></tr></thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($history as $item)
                            <tr><td class="px-4 py-3 font-bold">#{{ str_pad($item->id, 6, '0', STR_PAD_LEFT) }}<div class="mt-1 text-xs {{ $item->status === 'open' ? 'text-green-700' : 'text-gray-500' }}">{{ strtoupper($item->status) }}</div></td><td class="px-4 py-3"><strong>{{ $item->register->name }}</strong><div class="text-xs text-gray-500">{{ $item->user->name }}</div></td><td class="whitespace-nowrap px-4 py-3 text-xs text-gray-600">{{ $item->opened_at->timezone(config('cash.timezone'))->format('d/m/Y H:i') }}<div>{{ $item->closed_at?->timezone(config('cash.timezone'))?->format('d/m/Y H:i') ?: '—' }}</div></td><td class="px-4 py-3 text-right font-semibold">{{ $currencySymbol }} {{ number_format($item->opening_amount, 2) }}</td><td class="px-4 py-3 text-right">{{ $item->expected_amount !== null ? $currencySymbol.' '.number_format($item->expected_amount, 2) : '—' }}</td><td class="px-4 py-3 text-right">{{ $item->closing_amount !== null ? $currencySymbol.' '.number_format($item->closing_amount, 2) : '—' }}</td><td class="px-4 py-3 text-right font-bold">{{ $item->difference !== null ? $currencySymbol.' '.number_format($item->difference, 2) : '—' }}</td><td class="px-4 py-3 text-right"><button wire:click="viewSession({{ $item->id }})" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-bold hover:bg-gray-100">Ver detalle</button></td></tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-10 text-center text-gray-500">No hay sesiones para los filtros seleccionados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-300 bg-white px-4 py-3">{{ $history->links() }}</div>
        </section>
    </div>

    <x-dialog-modal name="cash-opening" maxWidth="3xl">
        <x-slot name="title"><div><h2 class="text-xl font-bold text-gray-900">ABRIR CAJA</h2><p class="mt-1 text-sm font-normal text-gray-600">Cuenta el dinero disponible antes de comenzar las operaciones.</p></div></x-slot>
        <x-slot name="content">
            <div class="mb-4 grid gap-3 rounded-md border border-gray-300 bg-gray-100 p-3 sm:grid-cols-3"><div><p class="text-xs font-semibold text-gray-500">Caja</p><p class="font-bold">{{ $registers->firstWhere('code', $registerCode)?->name }}</p></div><div><p class="text-xs font-semibold text-gray-500">Usuario</p><p class="font-bold">{{ auth()->user()->name }}</p></div><div><p class="text-xs font-semibold text-gray-500">Moneda</p><p class="font-bold">{{ $registers->firstWhere('code', $registerCode)?->currency_code }}</p></div></div>
            <div class="grid gap-4 lg:grid-cols-2">
                @foreach([['BILLETES', $banknotes], ['MONEDAS', $coins]] as [$title, $group])
                    <section class="overflow-hidden rounded-md border border-gray-300"><h3 class="bg-gray-900 px-4 py-3 text-sm font-bold text-white">{{ $title }}</h3><div class="divide-y divide-gray-200">@foreach($group as $denomination)<label class="grid grid-cols-[1fr_90px_1fr] items-center gap-3 px-4 py-2"><span class="font-bold">{{ $currencySymbol }} {{ number_format($denomination->value, 2) }}</span><input aria-label="Cantidad de {{ $currencySymbol }} {{ $denomination->value }}" inputmode="numeric" type="number" min="0" max="{{ config('cash.max_denomination_quantity') }}" step="1" wire:model.live.debounce.150ms="openingCounts.{{ $denomination->id }}" class="h-9 rounded-md border-gray-300 px-2 text-center text-sm"><span class="text-right font-semibold text-gray-700">{{ $currencySymbol }} {{ number_format($this->lineSubtotal($denomination->id, 'opening'), 2) }}</span></label>@endforeach</div></section>
                @endforeach
            </div>
            @error('denominationCounts')<p class="mt-3 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
            <div class="mt-4 flex flex-col gap-3 rounded-md bg-gray-950 p-4 text-white sm:flex-row sm:items-center sm:justify-between"><div><p class="text-xs font-bold uppercase tracking-wider text-gray-400">Total contado</p><p class="mt-1 text-3xl font-black">{{ $currencySymbol }} {{ number_format($this->openingTotal, 2) }}</p></div><button type="button" wire:click="clearOpeningCount" class="rounded-md border border-white/30 px-4 py-2 text-sm font-bold">Limpiar conteo</button></div>
            <label class="mt-4 block"><span class="text-xs font-semibold text-gray-700">Observaciones (opcional)</span><textarea wire:model="openingNotes" rows="2" class="mt-1 w-full rounded-md border-gray-300" maxlength="1000"></textarea></label>
        </x-slot>
        <x-slot name="footer"><button type="button" wire:click="$dispatch('close-modal', 'cash-opening')" class="rounded-md border border-gray-300 px-4 py-2.5 text-sm font-bold">Cancelar</button><button type="button" wire:click="openSession" wire:loading.attr="disabled" wire:target="openSession" class="ms-3 rounded-md bg-green-600 px-5 py-2.5 text-sm font-bold text-white disabled:opacity-50"><span wire:loading.remove wire:target="openSession">Abrir caja</span><span wire:loading wire:target="openSession">Procesando...</span></button></x-slot>
    </x-dialog-modal>

    <x-dialog-modal name="cash-movement" maxWidth="lg">
        <x-slot name="title"><div><h2 class="text-xl font-bold">REGISTRAR MOVIMIENTO</h2><p class="mt-1 text-sm font-normal text-gray-600">El saldo se valida nuevamente en el servidor.</p></div></x-slot>
        <x-slot name="content">
            <div class="grid gap-4 sm:grid-cols-2"><label><span class="text-xs font-semibold text-gray-700">Tipo</span><select wire:model.live="movementType" class="mt-1 w-full rounded-md border-gray-300"><option value="income">Ingreso</option><option value="deposit">Depósito</option><option value="expense">Gasto</option><option value="withdrawal">Retiro</option></select></label><label><span class="text-xs font-semibold text-gray-700">Monto</span><input wire:model.live.debounce.200ms="movementAmount" inputmode="decimal" class="mt-1 w-full rounded-md border-gray-300" placeholder="0.00"></label></div>
            <label class="mt-4 block"><span class="text-xs font-semibold text-gray-700">Motivo</span><textarea wire:model="movementReason" rows="3" maxlength="1000" class="mt-1 w-full rounded-md border-gray-300"></textarea></label>
            <label class="mt-4 block"><span class="text-xs font-semibold text-gray-700">Referencia (opcional)</span><input wire:model="movementReference" maxlength="255" class="mt-1 w-full rounded-md border-gray-300"></label>
            @error('movementAmount')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror @error('movementReason')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
            @if($session)<div class="mt-4 grid grid-cols-2 gap-3 rounded-md border border-gray-300 bg-gray-100 p-3"><div><p class="text-xs text-gray-500">Saldo actual</p><p class="font-black">{{ $currencySymbol }} {{ number_format($breakdown['expected'], 2) }}</p></div><div><p class="text-xs text-gray-500">Saldo después</p><p class="font-black">{{ $this->projectedBalance !== null ? $currencySymbol.' '.number_format($this->projectedBalance, 2) : '—' }}</p></div></div>@endif
        </x-slot>
        <x-slot name="footer"><button type="button" wire:click="$dispatch('close-modal', 'cash-movement')" class="rounded-md border border-gray-300 px-4 py-2.5 text-sm font-bold">Cancelar</button><button type="button" wire:click="addMovement" wire:loading.attr="disabled" wire:target="addMovement" class="ms-3 rounded-md bg-gray-900 px-5 py-2.5 text-sm font-bold text-white"><span wire:loading.remove wire:target="addMovement">Registrar</span><span wire:loading wire:target="addMovement">Procesando...</span></button></x-slot>
    </x-dialog-modal>

    <x-dialog-modal name="cash-closing" maxWidth="3xl">
        <x-slot name="title"><div><h2 class="text-xl font-bold">CIERRE DE CAJA</h2><p class="mt-1 text-sm font-normal text-gray-600">{{ $closingPreview ? 'Revisa la conciliación antes de confirmar.' : 'Cuenta físicamente el dinero. El esperado permanecerá oculto hasta terminar.' }}</p></div></x-slot>
        <x-slot name="content">
            @if(!$closingPreview)
                <div class="mb-4 rounded-md border border-blue-200 bg-blue-50 p-3 text-sm text-blue-800"><strong>Conteo ciego activo.</strong> El sistema revelará el saldo esperado únicamente después de revisar las denominaciones.</div>
                <div class="grid gap-4 lg:grid-cols-2">
                    @foreach([['BILLETES', $banknotes], ['MONEDAS', $coins]] as [$title, $group])
                        <section class="overflow-hidden rounded-md border border-gray-300"><h3 class="bg-gray-900 px-4 py-3 text-sm font-bold text-white">{{ $title }}</h3><div class="divide-y divide-gray-200">@foreach($group as $denomination)<label class="grid grid-cols-[1fr_90px_1fr] items-center gap-3 px-4 py-2"><span class="font-bold">{{ $currencySymbol }} {{ number_format($denomination->value, 2) }}</span><input aria-label="Cantidad de cierre {{ $currencySymbol }} {{ $denomination->value }}" inputmode="numeric" type="number" min="0" max="{{ config('cash.max_denomination_quantity') }}" step="1" wire:model.live.debounce.150ms="closingCounts.{{ $denomination->id }}" class="h-9 rounded-md border-gray-300 px-2 text-center text-sm"><span class="text-right font-semibold">{{ $currencySymbol }} {{ number_format($this->lineSubtotal($denomination->id, 'closing'), 2) }}</span></label>@endforeach</div></section>
                    @endforeach
                </div>
                @error('denominationCounts')<p class="mt-3 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                <div class="mt-4 flex items-center justify-between rounded-md bg-gray-950 p-4 text-white"><div><p class="text-xs font-bold uppercase text-gray-400">Total contado</p><p class="text-3xl font-black">{{ $currencySymbol }} {{ number_format($this->closingTotal, 2) }}</p></div><button type="button" wire:click="clearClosingCount" class="rounded-md border border-white/30 px-4 py-2 text-sm font-bold">Limpiar</button></div>
            @else
                <div class="grid gap-3 sm:grid-cols-3"><div class="rounded-md border border-gray-300 p-4"><p class="text-xs font-semibold text-gray-500">Efectivo esperado</p><p class="mt-2 text-xl font-black">{{ $currencySymbol }} {{ number_format($closingPreview['expected'], 2) }}</p></div><div class="rounded-md border border-gray-300 p-4"><p class="text-xs font-semibold text-gray-500">Efectivo contado</p><p class="mt-2 text-xl font-black">{{ $currencySymbol }} {{ number_format($closingPreview['counted'], 2) }}</p></div><div class="rounded-md border-2 p-4 {{ $closingPreview['status'] === 'CUADRA' ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50' }}"><p class="text-xs font-bold">{{ $closingPreview['status'] }}</p><p class="mt-2 text-xl font-black">{{ $currencySymbol }} {{ number_format($closingPreview['difference'], 2) }}</p></div></div>
                @if($closingPreview['status'] !== 'CUADRA')<label class="mt-4 block"><span class="text-sm font-bold text-gray-800">Motivo obligatorio de la diferencia</span><textarea wire:model="differenceReason" rows="3" class="mt-1 w-full rounded-md border-gray-300" placeholder="Describe concretamente la causa (mínimo 10 caracteres)"></textarea></label>@error('differenceReason')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror@endif
            @endif
        </x-slot>
        <x-slot name="footer"><button type="button" wire:click="$dispatch('close-modal', 'cash-closing')" class="rounded-md border border-gray-300 px-4 py-2.5 text-sm font-bold">Cancelar</button>@if(!$closingPreview)<button type="button" wire:click="reviewClosing" wire:loading.attr="disabled" wire:target="reviewClosing" class="ms-3 rounded-md bg-blue-600 px-5 py-2.5 text-sm font-bold text-white"><span wire:loading.remove wire:target="reviewClosing">Revisar conteo</span><span wire:loading wire:target="reviewClosing">Calculando...</span></button>@else<button type="button" wire:click="closeSession" wire:loading.attr="disabled" wire:target="closeSession" class="ms-3 rounded-md bg-amber-600 px-5 py-2.5 text-sm font-bold text-white"><span wire:loading.remove wire:target="closeSession">Confirmar cierre</span><span wire:loading wire:target="closeSession">Procesando...</span></button>@endif</x-slot>
    </x-dialog-modal>

    <x-dialog-modal name="cash-closing-result" maxWidth="lg">
        <x-slot name="title"><h2 class="text-xl font-bold">CAJA CERRADA</h2></x-slot>
        <x-slot name="content">@if($closedSummary)<div class="text-center"><div class="text-sm font-bold {{ $closedSummary['status'] === 'CUADRA' ? 'text-green-700' : 'text-red-700' }}">{{ $closedSummary['status'] }}</div><p class="mt-3 text-3xl font-black">{{ $currencySymbol }} {{ number_format($closedSummary['difference'], 2) }}</p><div class="mt-5 grid grid-cols-2 gap-3 text-left"><div class="rounded-md bg-gray-100 p-3"><p class="text-xs text-gray-500">Esperado</p><p class="font-bold">{{ $currencySymbol }} {{ number_format($closedSummary['expected'], 2) }}</p></div><div class="rounded-md bg-gray-100 p-3"><p class="text-xs text-gray-500">Contado</p><p class="font-bold">{{ $currencySymbol }} {{ number_format($closedSummary['counted'], 2) }}</p></div></div></div>@endif</x-slot>
        <x-slot name="footer"><button wire:click="$dispatch('close-modal', 'cash-closing-result')" class="rounded-md bg-gray-900 px-5 py-2.5 text-sm font-bold text-white">Aceptar</button></x-slot>
    </x-dialog-modal>

    <x-dialog-modal name="cash-session-detail" maxWidth="3xl">
        <x-slot name="title"><div><h2 class="text-xl font-bold">DETALLE DE SESIÓN</h2><p class="text-sm font-normal text-gray-500">Consulta histórica de solo lectura.</p></div></x-slot>
        <x-slot name="content">
            @if($selectedHistorySession)
                <div class="grid gap-3 sm:grid-cols-4"><div class="rounded-md bg-gray-100 p-3"><p class="text-xs text-gray-500">Caja</p><p class="font-bold">{{ $selectedHistorySession->register->name }}</p></div><div class="rounded-md bg-gray-100 p-3"><p class="text-xs text-gray-500">Operador</p><p class="font-bold">{{ $selectedHistorySession->user->name }}</p></div><div class="rounded-md bg-gray-100 p-3"><p class="text-xs text-gray-500">Apertura</p><p class="font-bold">{{ $selectedHistorySession->opened_at->format('d/m/Y H:i') }}</p></div><div class="rounded-md bg-gray-100 p-3"><p class="text-xs text-gray-500">Estado</p><p class="font-bold">{{ strtoupper($selectedHistorySession->status) }}</p></div></div>
                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    @foreach([['CONTEO DE APERTURA', $selectedHistorySession->openingCount], ['CONTEO DE CIERRE', $selectedHistorySession->closingCount]] as [$title, $count])
                        <section class="rounded-md border border-gray-300"><h3 class="bg-gray-900 px-4 py-2 text-sm font-bold text-white">{{ $title }}</h3>@if($count)<div class="divide-y divide-gray-200">@foreach($count->lines as $line)@if($line->quantity > 0)<div class="flex justify-between px-4 py-2 text-sm"><span>{{ $currencySymbol }} {{ number_format($line->denomination->value, 2) }} × {{ $line->quantity }}</span><strong>{{ $currencySymbol }} {{ number_format($line->subtotal, 2) }}</strong></div>@endif @endforeach</div><div class="border-t border-gray-300 p-3 text-right font-black">Total {{ $currencySymbol }} {{ number_format($count->total, 2) }}</div>@else<p class="p-4 text-sm text-gray-500">Sin conteo registrado (sesión histórica anterior a Fase 4.5).</p>@endif</section>
                    @endforeach
                </div>
                <section class="mt-4 overflow-hidden rounded-md border border-gray-300"><h3 class="bg-gray-900 px-4 py-2 text-sm font-bold text-white">MOVIMIENTOS</h3><div class="max-h-60 overflow-auto divide-y divide-gray-200">@forelse($selectedHistorySession->movements as $movement)<div class="flex justify-between gap-4 px-4 py-2 text-sm"><span>{{ strtoupper($movement->type) }} · {{ $movement->reason }}</span><strong>{{ $currencySymbol }} {{ number_format($movement->amount, 2) }}</strong></div>@empty<p class="p-4 text-sm text-gray-500">Sin movimientos.</p>@endforelse</div></section>
                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <section class="overflow-hidden rounded-md border border-gray-300"><h3 class="bg-gray-900 px-4 py-2 text-sm font-bold text-white">VENTAS DEL TURNO</h3><div class="max-h-48 overflow-auto divide-y divide-gray-200">@forelse($selectedHistorySession->sales as $sale)<div class="flex justify-between px-4 py-2 text-sm"><span>Venta #{{ $sale->id }} · {{ $sale->customer?->name }}</span><strong>{{ $currencySymbol }} {{ number_format($sale->total, 2) }}</strong></div>@empty<p class="p-4 text-sm text-gray-500">Sin ventas vinculadas.</p>@endforelse</div></section>
                    <section class="overflow-hidden rounded-md border border-gray-300"><h3 class="bg-gray-900 px-4 py-2 text-sm font-bold text-white">REFUNDS DEL TURNO</h3><div class="max-h-48 overflow-auto divide-y divide-gray-200">@forelse($selectedHistorySession->refunds as $refund)<div class="flex justify-between px-4 py-2 text-sm"><span>Refund #{{ $refund->id }} · {{ $refund->paymentMethod?->name }}</span><strong>{{ $currencySymbol }} {{ number_format($refund->amount, 2) }}</strong></div>@empty<p class="p-4 text-sm text-gray-500">Sin refunds vinculados.</p>@endforelse</div></section>
                </div>
                <section class="mt-4 overflow-hidden rounded-md border border-gray-300"><h3 class="bg-gray-900 px-4 py-2 text-sm font-bold text-white">AUDITORÍA RELEVANTE</h3><div class="max-h-52 overflow-auto divide-y divide-gray-200">@forelse($selectedAudit as $audit)<div class="px-4 py-2 text-sm"><div class="flex justify-between gap-3"><strong>{{ $audit->event }}</strong><span class="text-xs text-gray-500">{{ $audit->created_at->timezone(config('cash.timezone'))->format('d/m/Y H:i:s') }}</span></div><p class="mt-1 text-xs text-gray-500">Actor #{{ $audit->user_id ?: 'sistema' }} · IP {{ $audit->ip_address ?: '—' }}</p></div>@empty<p class="p-4 text-sm text-gray-500">Sin eventos de auditoría asociados.</p>@endforelse</div></section>
                @if($selectedHistorySession->status === 'closed')<div class="mt-4 grid gap-3 sm:grid-cols-3"><div class="rounded-md border p-3"><p class="text-xs text-gray-500">Esperado</p><p class="font-black">{{ $currencySymbol }} {{ number_format($selectedHistorySession->expected_amount, 2) }}</p></div><div class="rounded-md border p-3"><p class="text-xs text-gray-500">Contado</p><p class="font-black">{{ $currencySymbol }} {{ number_format($selectedHistorySession->closing_amount, 2) }}</p></div><div class="rounded-md border p-3"><p class="text-xs text-gray-500">Diferencia</p><p class="font-black">{{ $currencySymbol }} {{ number_format($selectedHistorySession->difference, 2) }}</p><p class="mt-1 text-xs text-gray-600">{{ $selectedHistorySession->closing_notes }}</p></div></div>@endif
            @endif
        </x-slot>
        <x-slot name="footer"><button wire:click="$dispatch('close-modal', 'cash-session-detail')" class="rounded-md bg-gray-900 px-5 py-2.5 text-sm font-bold text-white">Cerrar</button></x-slot>
    </x-dialog-modal>
</div>
