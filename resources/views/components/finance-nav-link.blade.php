@props(['href', 'active' => false])

<a href="{{ $href }}" wire:navigate
    {{ $attributes->class([
        'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition',
        'bg-white/10 text-white shadow-sm ring-1 ring-white/10' => $active,
        'text-slate-400 hover:bg-white/[0.06] hover:text-white' => ! $active,
    ]) }}>
    <span @class([
        'flex size-8 shrink-0 items-center justify-center rounded-lg transition',
        'bg-blue-500 text-white' => $active,
        'bg-white/[0.05] text-slate-400 group-hover:bg-white/10 group-hover:text-white' => ! $active,
    ])>
        {{ $icon }}
    </span>
    <span class="truncate">{{ $slot }}</span>
    @if ($active)
        <span class="ms-auto size-1.5 rounded-full bg-blue-300"></span>
    @endif
</a>
