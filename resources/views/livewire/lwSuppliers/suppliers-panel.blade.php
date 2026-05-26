<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg m-2 h-[calc(100vh-64px)]"> <!-- CONTENEDOR MAESTRO -->

	<!-- Vista Superior -->
	<div class="flex items-center w-full my-3">
		<h2 class="text-xl font-semibold my-2 ml-6 text-gray-900 ms- w-full">Panel de control: Proveedores</h2>

		<x-primary-button class="mx-6 w-auto place-items-end bg-gray-900 rounded-md z-10" wire:click="create">
			<div class="flex items-center">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 fill-white">
					<path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 9a.75.75 0 0 0-1.5 0v2.25H9a.75.75 0 0 0 0 1.5h2.25V15a.75.75 0 0 0 1.5 0v-2.25H15a.75.75 0 0 0 0-1.5h-2.25V9Z" clip-rule="evenodd" />
				</svg>
				<span class="text-md whitespace-nowrap text-white ms-3">Agregar registro</span>
			</div>
		</x-primary-button>
	</div>

	<div class="border-t border-gray-300"></div> <!-- Separador -->

	<!-- Tabla de datos -->
	<div class="m-6 bg-white border border-gray-300 rounded-lg shadow-md overflow-hidden">
		<table class="min-w-full bg-white border-collapse p-6">
			<thead>
				<tr>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white min-w-52">{{ __('Nombre') }}</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white min-w-72">{{ __('Contacto') }}</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white min-w-36">{{ __('Telefono') }}</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white w-full">{{ __('Direccion') }}</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white w-10">{{ __('Acciones') }}</th>
				</tr>
			</thead>

			<tbody>
				@foreach($suppliers as $supplier)
					<tr class="hover:bg-gray-100">
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700">{{ $supplier->name }}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700">{{ $supplier->contact }}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-center text-sm text-gray-700">{{ $supplier->phone }}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700">{{ $supplier->address }}</td>

						<!-- BOTONES DE ACCION -->
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700">
							<div class="flex w-full justify-center">
								<a class="z-30 cursor-pointer" wire:click="update({{ $supplier->id }})">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 mx-2 fill-gray-700 hover:fill-yellow-500">
										<path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z" />
										<path d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z" />
									</svg>
								</a>
								<a class="z-30 cursor-pointer" wire:click="$dispatch('deleteSupplier', { id: {{ $supplier->id }} })">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 mx-2 fill-gray-700 hover:fill-red-600">
										<path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd" />
									</svg>
								</a>
							</div>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>

	<div class="mb-2">
		{{ $suppliers->links('custom-tailwind') }}
	</div>

	<!-- Formulario Modal -->
	<form wire:submit="save">
		<x-dialog-modal name="modal-form-supplier" maxWidth="sm"> 

			<x-slot name="title">
				<div class="flex justify-center mt-3">
					@if($isEditing)
						<h1 class="text-lg text-gray-900 border-b border-gray-300 pb-1 w-full text-center font-bold whitespace-nowrap" style="width: 50%">Editando registro</h1>
					@else
						<h1 class="text-lg text-gray-900 border-b border-gray-300 pb-1 w-full text-center font-bold whitespace-nowrap" style="width: 50%">Nuevo registro</h1>
					@endif
				</div>
			</x-slot>

			<x-slot name="content">
				<div class="mx-6 mt-2 w-auto">
					<x-input-label class="mb-1">Nombre</x-input-label>
					<x-text-input class="h-10 text-sm w-full" wire:model.live="supplier.name"></x-text-input>
					@error('supplier.name') <span class="text-red-600"> {{ $message }} </span> @enderror
				</div>

				<div class="mx-6 mt-2 w-auto">
					<x-input-label class="mb-1">Contacto</x-input-label>
					<x-text-input class="h-10 text-sm w-full" wire:model.live="supplier.contact"></x-text-input>
					@error('supplier.contact') <span class="text-red-600"> {{ $message }} </span> @enderror
				</div>

				<div class="mx-6 mt-2 w-auto">
					<x-input-label class="mb-1">Telefono</x-input-label>
					<x-text-input class="h-10 text-sm w-full" wire:model.live="supplier.phone"></x-text-input>
					@error('supplier.phone') <span class="text-red-600"> {{ $message }} </span> @enderror
				</div>

				<div class="mx-6 mt-2 w-auto">
					<x-input-label class="mb-1">Direccion</x-input-label>
					<x-text-input class="h-10 text-sm w-full" wire:model.live="supplier.address"></x-text-input>
					@error('supplier.address') <span class="text-red-600"> {{ $message }} </span> @enderror
				</div>
			</x-slot>

			<x-slot name="footer">
				<div class="py-2 w-auto items-end justify-center">
					<x-primary-button wire:click="$dispatch('close-modal', 'modal-form-supplier')" class="h-10 bg-red-600 hover:bg-red-500 mr-2">Cancelar</x-primary-button>
				</div>
				<div class="py-2 w-auto items-end justify-center">
					<x-primary-button type="submit" class="h-10 shadow-sm">Aceptar</x-primary-button>
				</div>
			</x-slot>
			</x-dialog-modal>
	</form>

	@script
	<script>
		$wire.on('deleteSupplier', (id) => {
			Swal.fire({
				title: "¿Está seguro?",
				text: "¡No podrá revertir esta acción!",
				icon: "warning",
				showCancelButton: true,
				confirmButtonColor: "#3085d6",
				cancelButtonColor: "#d33",
				confirmButtonText: "¡Sí, eliminar!",
				cancelButtonText: 'Cancelar'
			}).then((result) => {
				if (result.isConfirmed) {
					$wire.destroy(id)
					Swal.fire({
						title: "¡Eliminado!",
						text: "Registro eliminado con éxito",
						icon: "success"
					});
				}
			});

		});
	</script>
	@endscript
</div>