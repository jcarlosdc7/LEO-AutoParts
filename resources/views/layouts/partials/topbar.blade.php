<header class="fixed inset-x-0 top-0 z-50 h-16 border-b border-slate-200 bg-white shadow-sm transition-colors dark:border-slate-800 dark:bg-slate-950">
    <div class="flex h-full items-center gap-3 px-4 sm:px-6">
        <button type="button" @click="sidebarOpen = !sidebarOpen" class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-blue-500 lg:hidden" aria-label="Abrir menú">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
        </button>
        <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3 lg:w-56">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-950 p-1.5 shadow-sm ring-1 ring-slate-800 dark:bg-white dark:ring-white/20">
                <img src="{{ asset('images/brand/icon-light.png') }}" alt="LEO AutoParts" class="h-full w-full object-contain dark:hidden">
                <img src="{{ asset('images/brand/icon-dark.png') }}" alt="LEO AutoParts" class="hidden h-full w-full object-contain dark:block">
            </div>
            <div class="hidden min-w-0 sm:block">
                <p class="truncate text-sm font-bold leading-tight text-slate-950 dark:text-white">LEO AutoParts</p>
                <p class="truncate text-xs text-slate-500">Inventario y facturación</p>
            </div>
        </a>
        <div class="ml-auto flex items-center gap-2" x-data="{ accountOpen: false }">
            <button type="button" @click="toggleTheme" class="flex h-10 w-10 items-center justify-center rounded-xl text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white" :aria-label="theme === 'dark' ? 'Activar modo claro' : 'Activar modo oscuro'" :title="theme === 'dark' ? 'Modo claro' : 'Modo oscuro'">
                <svg x-show="theme === 'light'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 12a9 9 0 1 1-9-9 7 7 0 0 0 9 9Z"/></svg>
                <svg x-cloak x-show="theme === 'dark'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v2m0 14v2M3 12h2m14 0h2M5.64 5.64l1.42 1.42m9.88 9.88 1.42 1.42m0-12.72-1.42 1.42M7.06 16.94l-1.42 1.42M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/></svg>
            </button>
            <div class="hidden text-right sm:block">
                <p class="max-w-48 truncate text-sm font-semibold text-slate-800 dark:text-slate-100">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-500">{{ auth()->user()->role?->name ?? 'Usuario' }}</p>
            </div>
            <button @click="accountOpen = !accountOpen" @click.outside="accountOpen = false" class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-blue-600 text-sm font-bold text-white shadow-sm ring-4 ring-blue-50 transition hover:bg-blue-700 dark:ring-slate-800" aria-label="Menú de cuenta">
                @if(auth()->user()->avatar_path && Storage::disk('public')->exists(auth()->user()->avatar_path))<img src="{{ asset('storage/'.auth()->user()->avatar_path) }}" alt="{{ auth()->user()->name }}" class="h-full w-full object-cover">@else{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}@endif
            </button>
            <div x-cloak x-show="accountOpen" x-transition class="absolute right-4 top-14 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white p-1 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                <div class="border-b border-slate-100 px-3 py-2 sm:hidden"><p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p><p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p></div>
                <a href="{{ route('profile') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">Mi perfil</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="w-full rounded-lg px-3 py-2 text-left text-sm text-red-600 transition hover:bg-red-50">Cerrar sesión</button></form>
            </div>
        </div>
    </div>
</header>
