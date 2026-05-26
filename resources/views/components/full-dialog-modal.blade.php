@props(['id' => null, 'maxWidth' => null])

<x-full-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>

	<!-- Usar flex para controlar la disposición -->
	<div class="flex flex-col h-full">

		<!-- Título del modal -->
		<div class="text-lg font-medium text-gray-900">
			{{ $title }}
		</div>

		<!-- Contenido principal del modal, usa flex-grow para llenar el espacio disponible -->
		<div class="text-sm text-gray-600 flex-grow">
			{{ $content }}
		</div>

		<!-- Footer, pegado al fondo -->
		<div class="flex-shrink-0 bg-gray-100 text-end px-6 py-1">
			{{ $footer }}
		</div>
	</div>
</x-full-modal>
