<div class="leo-page">
	<div class="leo-container">
		<header class="leo-header">
			<div><h1 class="leo-title">Reportes</h1><p class="leo-subtitle">Exporta información operativa en formatos PDF y Excel.</p></div>
		</header>

	<!-- REPORTES EN PDF -->
	<div class="container mx-auto p-8 py-4">
		<div class="w-full flex justify-center">
			<div class="flex justify-center items-center m-6 border-b border-gray-300" style="width: 50%">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-7 mb-1 me-2 fill-red-500">
					<path d="M5.523 12.424q.21-.124.459-.238a8 8 0 0 1-.45.606c-.28.337-.498.516-.635.572l-.035.012a.3.3 0 0 1-.026-.044c-.056-.11-.054-.216.04-.36.106-.165.319-.354.647-.548m2.455-1.647q-.178.037-.356.078a21 21 0 0 0 .5-1.05 12 12 0 0 0 .51.858q-.326.048-.654.114m2.525.939a4 4 0 0 1-.435-.41q.344.007.612.054c.317.057.466.147.518.209a.1.1 0 0 1 .026.064.44.44 0 0 1-.06.2.3.3 0 0 1-.094.124.1.1 0 0 1-.069.015c-.09-.003-.258-.066-.498-.256M8.278 6.97c-.04.244-.108.524-.2.829a5 5 0 0 1-.089-.346c-.076-.353-.087-.63-.046-.822.038-.177.11-.248.196-.283a.5.5 0 0 1 .145-.04c.013.03.028.092.032.198q.008.183-.038.465z"/>
					<path fill-rule="evenodd" d="M4 0h5.293A1 1 0 0 1 10 .293L13.707 4a1 1 0 0 1 .293.707V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2m5.5 1.5v2a1 1 0 0 0 1 1h2zM4.165 13.668c.09.18.23.343.438.419.207.075.412.04.58-.03.318-.13.635-.436.926-.786.333-.401.683-.927 1.021-1.51a11.7 11.7 0 0 1 1.997-.406c.3.383.61.713.91.95.28.22.603.403.934.417a.86.86 0 0 0 .51-.138c.155-.101.27-.247.354-.416.09-.181.145-.37.138-.563a.84.84 0 0 0-.2-.518c-.226-.27-.596-.4-.96-.465a5.8 5.8 0 0 0-1.335-.05 11 11 0 0 1-.98-1.686c.25-.66.437-1.284.52-1.794.036-.218.055-.426.048-.614a1.24 1.24 0 0 0-.127-.538.7.7 0 0 0-.477-.365c-.202-.043-.41 0-.601.077-.377.15-.576.47-.651.823-.073.34-.04.736.046 1.136.088.406.238.848.43 1.295a20 20 0 0 1-1.062 2.227 7.7 7.7 0 0 0-1.482.645c-.37.22-.699.48-.897.787-.21.326-.275.714-.08 1.103"/>
				</svg>
				<span class="text-2xl text-gray-900 pb-1 text-center font-bold whitespace-nowrap">Reportes en PDF</span>
			</div>
		</div>
		<!-- Contenedor para los botones -->
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
			
			<!-- Botón 1: Reporte de Ventas -->
			<div class="flex justify-center hover:cursor-pointer">
				<a wire:click="downloadSalesPdf" class="bg-gray-900 text-white w-full p-6 rounded-lg shadow-lg flex items-center justify-start space-x-4 hover:bg-red-900">
					<svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
						<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4m6-6v6m6-4v4m6-6v6M3 11l6-5 6 5 5.5-5.5"/>
					  </svg>
					  
					<span class="text-lg font-semibold">Reporte de Ventas</span>
				</a>
			</div>
	
			<!-- Botón 2: Reporte de Productos -->
			<div class="flex justify-center hover:cursor-pointer">
				<a wire:click="$dispatch('open-modal', 'modal-form-paramProductsPDF')" class="bg-gray-900 text-white w-full p-6 rounded-lg shadow-lg flex items-center justify-start space-x-4 hover:bg-red-900">
					<svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
						<path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M2.9917 4.9834V18.917M9.96265 4.9834V18.917M15.9378 4.9834V18.917m2.9875-13.9336V18.917"/>
						<path stroke="currentColor" stroke-linecap="round" d="M5.47925 4.4834V19.417m1.9917-14.9336V19.417M21.4129 4.4834V19.417M13.4461 4.4834V19.417"/>
					  </svg>
					  
					<span class="text-lg font-semibold">Reporte de Productos</span>
				</a>
			</div>
	
			<!-- Botón 3: Reporte de Clientes -->
			<div class="flex justify-center hover:cursor-pointer">
				<a wire:click="downloadCustomersPdf" class="bg-gray-900 text-white w-full p-6 rounded-lg shadow-lg flex items-center justify-start space-x-4 hover:bg-red-900">
					<svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
						<path fill-rule="evenodd" d="M4 4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H4Zm10 5a1 1 0 0 1 1-1h3a1 1 0 1 1 0 2h-3a1 1 0 0 1-1-1Zm0 3a1 1 0 0 1 1-1h3a1 1 0 1 1 0 2h-3a1 1 0 0 1-1-1Zm0 3a1 1 0 0 1 1-1h3a1 1 0 1 1 0 2h-3a1 1 0 0 1-1-1Zm-8-5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm1.942 4a3 3 0 0 0-2.847 2.051l-.044.133-.004.012c-.042.126-.055.167-.042.195.006.013.02.023.038.039.032.025.08.064.146.155A1 1 0 0 0 6 17h6a1 1 0 0 0 .811-.415.713.713 0 0 1 .146-.155c.019-.016.031-.026.038-.04.014-.027 0-.068-.042-.194l-.004-.012-.044-.133A3 3 0 0 0 10.059 14H7.942Z" clip-rule="evenodd"/>
					  </svg>
					  
					<span class="text-lg font-semibold">Reporte de Clientes</span>
				</a>
			</div>
	
			<!-- Botón 4: Reporte de Usuarios -->
			<div class="flex justify-center hover:cursor-pointer">
				<a wire:click="downloadUsersPdf" class="bg-gray-900 text-white w-full p-6 rounded-lg shadow-lg flex items-center justify-start space-x-4 hover:bg-red-900">
					<svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
						<path fill-rule="evenodd" d="M12 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-2 9a4 4 0 0 0-4 4v1a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1a4 4 0 0 0-4-4h-4Z" clip-rule="evenodd"/>
					  </svg>
					  
					<span class="text-lg font-semibold">Reporte de Usuarios</span>
				</a>
			</div>
	
			<!-- Botón 5: Reporte de Pagos -->
			<div class="flex justify-center hover:cursor-pointer">
				<a wire:click="downloadPaymentsPdf" class="bg-gray-900 text-white w-full p-6 rounded-lg shadow-lg flex items-center justify-start space-x-4 hover:bg-red-900">
					<svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
						<path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M8 7V6a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1h-1M3 18v-7a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-3.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>
					  </svg>
					  
					<span class="text-lg font-semibold">Reporte de Pagos</span>
				</a>
			</div>
	
			<!-- Botón 6: Reporte de Stock -->
			<div class="flex justify-center hover:cursor-pointer">
				<a wire:click="downloadStockPdf" class="bg-gray-900 text-white w-full p-6 rounded-lg shadow-lg flex items-center justify-start space-x-4 hover:bg-red-900">
					<svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
						<path fill-rule="evenodd" d="M5.005 10.19a1 1 0 0 1 1 1v.233l5.998 3.464L18 11.423v-.232a1 1 0 1 1 2 0V12a1 1 0 0 1-.5.866l-6.997 4.042a1 1 0 0 1-1 0l-6.998-4.042a1 1 0 0 1-.5-.866v-.81a1 1 0 0 1 1-1ZM5 15.15a1 1 0 0 1 1 1v.232l5.997 3.464 5.998-3.464v-.232a1 1 0 1 1 2 0v.81a1 1 0 0 1-.5.865l-6.998 4.042a1 1 0 0 1-1 0L4.5 17.824a1 1 0 0 1-.5-.866v-.81a1 1 0 0 1 1-1Z" clip-rule="evenodd"/>
						<path d="M12.503 2.134a1 1 0 0 0-1 0L4.501 6.17A1 1 0 0 0 4.5 7.902l7.002 4.047a1 1 0 0 0 1 0l6.998-4.04a1 1 0 0 0 0-1.732l-6.997-4.042Z"/>
					  </svg>
					  
					<span class="text-lg font-semibold">Reporte de Stock</span>
				</a>
			</div>
			
		</div>
	</div>
	
	<!-- REPORTES EN EXCEL -->
	<div class="container mx-auto p-8 pt-0">
		<div class="w-full flex justify-center">
			<div class="flex justify-center items-center m-6 border-b border-gray-300" style="width: 50%">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-7 mb-1 me-2 fill-green-500">
					<path d="M6 12v-2h3v2z"/>
					<path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M3 9h10v1h-3v2h3v1h-3v2H9v-2H6v2H5v-2H3v-1h2v-2H3z"/>
				</svg>
				<span class="text-2xl text-gray-900 pb-1 text-center font-bold whitespace-nowrap">Reportes en EXCEL</span>
			</div>
		</div>
	
		<!-- Contenedor para los botones -->
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
			
			<!-- Botón 1: Reporte de Ventas -->
			<div class="flex justify-center hover:cursor-pointer">
				<a wire:click="downloadSalesExcel" class="bg-gray-900 text-white w-full p-6 rounded-lg shadow-lg flex items-center justify-start space-x-4 hover:bg-green-900">
					<svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
						<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4m6-6v6m6-4v4m6-6v6M3 11l6-5 6 5 5.5-5.5"/>
					  </svg>
					  
					<span class="text-lg font-semibold">Reporte de Ventas</span>
				</a>
			</div>
	
			<!-- Botón 2: Reporte de Productos -->
			<div class="flex justify-center hover:cursor-pointer">
				<a wire:click="$dispatch('open-modal', 'modal-form-paramProductsEXCEL')" class="bg-gray-900 text-white w-full p-6 rounded-lg shadow-lg flex items-center justify-start space-x-4 hover:bg-green-900">
					<svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
						<path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M2.9917 4.9834V18.917M9.96265 4.9834V18.917M15.9378 4.9834V18.917m2.9875-13.9336V18.917"/>
						<path stroke="currentColor" stroke-linecap="round" d="M5.47925 4.4834V19.417m1.9917-14.9336V19.417M21.4129 4.4834V19.417M13.4461 4.4834V19.417"/>
					  </svg>
					  
					<span class="text-lg font-semibold">Reporte de Productos</span>
				</a>
			</div>
	
			<!-- Botón 3: Reporte de Clientes -->
			<div class="flex justify-center hover:cursor-pointer">
				<a wire:click="downloadCustomersExcel" class="bg-gray-900 text-white w-full p-6 rounded-lg shadow-lg flex items-center justify-start space-x-4 hover:bg-green-900">
					<svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
						<path fill-rule="evenodd" d="M4 4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H4Zm10 5a1 1 0 0 1 1-1h3a1 1 0 1 1 0 2h-3a1 1 0 0 1-1-1Zm0 3a1 1 0 0 1 1-1h3a1 1 0 1 1 0 2h-3a1 1 0 0 1-1-1Zm0 3a1 1 0 0 1 1-1h3a1 1 0 1 1 0 2h-3a1 1 0 0 1-1-1Zm-8-5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm1.942 4a3 3 0 0 0-2.847 2.051l-.044.133-.004.012c-.042.126-.055.167-.042.195.006.013.02.023.038.039.032.025.08.064.146.155A1 1 0 0 0 6 17h6a1 1 0 0 0 .811-.415.713.713 0 0 1 .146-.155c.019-.016.031-.026.038-.04.014-.027 0-.068-.042-.194l-.004-.012-.044-.133A3 3 0 0 0 10.059 14H7.942Z" clip-rule="evenodd"/>
					  </svg>
					  
					<span class="text-lg font-semibold">Reporte de Clientes</span>
				</a>
			</div>
	
			<!-- Botón 4: Reporte de Usuarios -->
			<div class="flex justify-center hover:cursor-pointer">
				<a wire:click="downloadUsersExcel" class="bg-gray-900 text-white w-full p-6 rounded-lg shadow-lg flex items-center justify-start space-x-4 hover:bg-green-900">
					<svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
						<path fill-rule="evenodd" d="M12 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-2 9a4 4 0 0 0-4 4v1a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1a4 4 0 0 0-4-4h-4Z" clip-rule="evenodd"/>
					  </svg>
					  
					<span class="text-lg font-semibold">Reporte de Usuarios</span>
				</a>
			</div>
	
			<!-- Botón 5: Reporte de Pagos -->
			<div class="flex justify-center hover:cursor-pointer">
				<a wire:click="downloadPaymentsExcel" class="bg-gray-900 text-white w-full p-6 rounded-lg shadow-lg flex items-center justify-start space-x-4 hover:bg-green-900">
					<svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
						<path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M8 7V6a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1h-1M3 18v-7a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-3.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>
					  </svg>
					  
					<span class="text-lg font-semibold">Reporte de Pagos</span>
				</a>
			</div>
	
			<!-- Botón 6: Reporte de Stock -->
			<div class="flex justify-center hover:cursor-pointer">
				<a wire:click="downloadStockExcel" class="bg-gray-900 text-white w-full p-6 rounded-lg shadow-lg flex items-center justify-start space-x-4 hover:bg-green-900">
					<svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
						<path fill-rule="evenodd" d="M5.005 10.19a1 1 0 0 1 1 1v.233l5.998 3.464L18 11.423v-.232a1 1 0 1 1 2 0V12a1 1 0 0 1-.5.866l-6.997 4.042a1 1 0 0 1-1 0l-6.998-4.042a1 1 0 0 1-.5-.866v-.81a1 1 0 0 1 1-1ZM5 15.15a1 1 0 0 1 1 1v.232l5.997 3.464 5.998-3.464v-.232a1 1 0 1 1 2 0v.81a1 1 0 0 1-.5.865l-6.998 4.042a1 1 0 0 1-1 0L4.5 17.824a1 1 0 0 1-.5-.866v-.81a1 1 0 0 1 1-1Z" clip-rule="evenodd"/>
						<path d="M12.503 2.134a1 1 0 0 0-1 0L4.501 6.17A1 1 0 0 0 4.5 7.902l7.002 4.047a1 1 0 0 0 1 0l6.998-4.04a1 1 0 0 0 0-1.732l-6.997-4.042Z"/>
					  </svg>
					  
					<span class="text-lg font-semibold">Reporte de Stock</span>
				</a>
			</div>
			
		</div>
	</div>

	<form wire:submit="downloadProductsExcel">
		<x-dialog-modal name="modal-form-paramProductsEXCEL" maxWidth="md" class="p-6">
			<x-slot name="title">
				<div class="flex justify-center mt-2">
					<h1 class="text-lg text-gray-900 border-b border-gray-300 pb-1 w-full text-center font-bold whitespace-nowrap" style="width: 50%">Reporte de productos</h1>
				</div>
			</x-slot>

			<x-slot name="content">
				<div class="px-6">
					<span class="font-bold">Filtrar por:</span>
				</div>
				<div class="px-6 mb-4">
					{{-- <x-input-label class="mb-1">Categoría</x-input-label> --}}
					<x-select class="h-10 text-sm w-full border rounded" wire:model="selectedCategory">
						<option value="0">Todos</option>
						@foreach($categories as $category)
							<option value="{{ $category->id }}">{{ $category->name }}</option>
						@endforeach
					</x-select>
					@error('selectedCategory') <span class="text-red-600">{{ $message }}</span> @enderror
				</div>
			</x-slot>

			<x-slot name="footer">
				<div class="py-2 w-auto items-end justify-center">
					<x-primary-button type="submit" class="h-10 shadow-sm">Aceptar</x-primary-button>
				</div>
			</x-slot>
		</x-dialog-modal>
	</form>

	<form wire:submit="downloadProductsPdf">
		<x-dialog-modal name="modal-form-paramProductsPDF" maxWidth="md" class="p-6">
			<x-slot name="title">
				<div class="flex justify-center mt-2">
					<h1 class="text-lg text-gray-900 border-b border-gray-300 pb-1 w-full text-center font-bold whitespace-nowrap" style="width: 50%">Reporte de productos</h1>
				</div>
			</x-slot>

			<x-slot name="content">
				<div class="px-6">
					<span class="font-bold">Filtrar por:</span>
				</div>
				<div class="px-6 mb-4">
					{{-- <x-input-label class="mb-1">Categoría</x-input-label> --}}
					<x-select class="h-10 text-sm w-full border rounded" wire:model="selectedCategory">
						<option value="0">Todos</option>
						@foreach($categories as $category)
							<option value="{{ $category->id }}">{{ $category->name }}</option>
						@endforeach
					</x-select>
					@error('selectedCategory') <span class="text-red-600">{{ $message }}</span> @enderror
				</div>
			</x-slot>

			<x-slot name="footer">
				<div class="py-2 w-auto items-end justify-center">
					<x-primary-button type="submit" class="h-10 shadow-sm">Aceptar</x-primary-button>
				</div>
			</x-slot>
		</x-dialog-modal>
	</form>
	</div>
</div>
