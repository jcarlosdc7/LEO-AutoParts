<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<nav class="fixed inset-x-0 top-0 z-50 h-16 border-b border-gray-300 bg-white shadow-sm">
    <div class="flex h-full items-center justify-between px-4 sm:px-6">
        <div class="flex min-w-0 items-center gap-3">
            <button type="button" @click="$dispatch('toggle-finance-sidebar')" class="inline-flex size-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-50 lg:hidden" aria-label="Abrir menú">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3">
                <x-application-logo class="hidden h-9 w-auto text-slate-900 sm:block" />
                <div class="hidden h-8 w-px bg-slate-200 sm:block"></div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold text-slate-900">Centro de control</p>
                    <p class="truncate text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">Finanzas · Inventario · Ventas</p>
                </div>
            </a>
        </div>

        <div class="flex items-center gap-3">
            <div class="hidden items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 sm:flex">
                <span class="size-2 rounded-full bg-emerald-500"></span>
                Operación en línea
            </div>
            <x-dropdown align="right" width="56">
                <x-slot name="trigger">
                    <button class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-left transition hover:bg-slate-50">
                        <span class="flex size-8 items-center justify-center rounded-lg bg-slate-900 text-xs font-extrabold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="hidden min-w-0 sm:block">
                            <span class="block max-w-40 truncate text-xs font-bold text-slate-800" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></span>
                            <span class="block text-[10px] font-semibold text-slate-400">{{ auth()->user()->role?->name ?? 'Usuario' }}</span>
                        </span>
                        <svg class="size-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                    </button>
                </x-slot>
                <x-slot name="content">
                    <div class="border-b border-slate-100 px-4 py-3">
                        <p class="truncate text-sm font-bold text-slate-800">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                    </div>
                    <x-dropdown-link :href="route('profile')" wire:navigate>Mi perfil</x-dropdown-link>
                    <button wire:click="logout" class="w-full text-start"><x-dropdown-link>Cerrar sesión</x-dropdown-link></button>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</nav>
