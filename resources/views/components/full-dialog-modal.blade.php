@props(['id' => null, 'maxWidth' => null])

<x-full-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>

	<!-- Usar flex para controlar la disposición -->
	<div class="flex h-full min-h-0 flex-col">

		<!-- Título del modal -->
		<div class="flex-shrink-0 border-b border-slate-100 px-5 pb-4 pt-5 text-lg font-medium text-gray-900 dark:border-slate-800 sm:px-6 sm:pt-6">
			{{ $title }}
		</div>

		<!-- Contenido principal del modal, usa flex-grow para llenar el espacio disponible -->
		<div class="min-h-0 flex-grow overflow-y-auto px-5 py-5 text-sm text-gray-600 sm:px-6 sm:py-6">
			{{ $content }}
		</div>

		<!-- Footer, pegado al fondo -->
		<div class="flex flex-shrink-0 flex-wrap justify-end gap-2 border-t border-slate-200 bg-gray-100 px-5 py-4 text-end dark:border-slate-800 sm:px-6">
			{{ $footer }}
		</div>
	</div>
</x-full-modal>
