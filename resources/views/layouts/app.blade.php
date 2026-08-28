<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'LEO AutoParts') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body class="bg-[#f4f7fb] font-sans antialiased">
        <div x-data="{ financeSidebarOpen: false }" x-on:toggle-finance-sidebar.window="financeSidebarOpen = ! financeSidebarOpen" class="min-h-screen">
            <livewire:layout.navigation />
            <livewire:layout.sidebar />

            @if (isset($header))
                <header class="border-b border-slate-200 bg-white pt-16 lg:pl-64">
                    <div class="mx-auto max-w-screen-2xl px-4 py-5 sm:px-6 lg:px-8">{{ $header }}</div>
                </header>
            @endif

            <main class="min-h-screen pt-16 lg:pl-64">
                {{ $slot }}
            </main>
        </div>

        @livewireScripts
    </body>
</html>
