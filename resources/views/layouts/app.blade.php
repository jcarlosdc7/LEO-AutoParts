<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<meta name="theme-color" content="#020617">
		<title>{{ config('app.name', 'Laravel') }}</title>
		<link rel="preconnect" href="https://fonts.bunny.net">
		<link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
		<script>
			(() => {
				const stored = localStorage.getItem('leo-theme');
				const dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
				document.documentElement.classList.toggle('dark', dark);
				document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
			})();
		</script>
		@livewireStyles
		@vite(['resources/css/app.css', 'resources/js/app.js'])
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	</head>
	<body class="bg-slate-100 font-sans text-slate-800 antialiased transition-colors duration-300 dark:bg-slate-950 dark:text-slate-200">
		<div x-data="{
			sidebarOpen: false,
			theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
			toggleTheme() {
				this.theme = this.theme === 'dark' ? 'light' : 'dark';
				document.documentElement.classList.toggle('dark', this.theme === 'dark');
				document.documentElement.style.colorScheme = this.theme;
				localStorage.setItem('leo-theme', this.theme);
			}
		}" class="min-h-screen">
            @include('layouts.partials.topbar')
            @include('layouts.partials.sidebar')
            <div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-slate-950/60 backdrop-blur-sm lg:hidden"></div>
			@if (isset($header))
				<header class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 lg:pl-64">
					<div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
						{{ $header }}
					</div>
				</header>
			@endif
            <main class="min-h-screen pt-16 transition-[padding] duration-300 lg:pl-64">
                {{ $slot }}
            </main>
		</div>
		@livewireScripts
	</body>
</html>
