<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg m-2 h-[calc(100vh-64px)]"> <!-- CONTENEDOR MAESTRO -->

	<!-- Vista Superior -->
	<div class="flex items-center w-full my-3">
		<h2 class="text-xl font-semibold my-2 ml-6 text-gray-900 ms- w-full">Panel de control: Clientes</h2>

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
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">{{ __('DNI/RUC') }}</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">{{ __('Nombre') }}</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">{{ __('Email') }}</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">{{ __('Teléfono') }}</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">{{ __('Dirección') }}</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">{{ __('Ciudad') }}</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">{{ __('Tipo de Cliente') }}</th>
					@if(!(auth()->user()->role_id === 3))
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white w-10">{{ __('Acciones') }}</th>
					@endif
				</tr>
			</thead>

			<tbody>
				@foreach($customers as $customer)
					<tr class="hover:bg-gray-100">
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700">{{ $customer->dni_ruc}}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700">{{ $customer->name }}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700">{{ $customer->email }}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700">{{ $customer->phone }}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700">{{ $customer->address }}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700">{{ $customer->city }}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700">{{ $this->getCustomerType($customer->customer_type_id) }}</td>

						@if(!(auth()->user()->role_id === 3))
						<!-- BOTONES DE ACCION -->
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700">
							<div class="flex w-full justify-center">
								<a class="z-30 cursor-pointer" wire:click="update({{ $customer->id }})">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 mx-2 fill-gray-700 hover:fill-yellow-500">
										<path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z" />
										<path d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z" />
									</svg>
								</a>
								<a class="z-30 cursor-pointer" wire:click="$dispatch('deleteCustomer', { id: {{ $customer->id }} })">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 mx-2 fill-gray-700 hover:fill-red-600">
										<path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd" />
									</svg>
								</a>

							</div>
						</td>
						@endif
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>

	<div class="mb-2">
		{{ $customers->links('custom-tailwind') }}
	</div>

	<!-- Formulario Modal -->
	<form wire:submit="save">
		<x-dialog-modal name="modal-form-customer" maxWidth="md"> 
			
			<x-slot name="title">
				<div class="flex justify-center mt-2">
					@if($isEditing)
						<h1 class="text-lg text-gray-900 border-b border-gray-300 pb-1 w-full text-center font-bold whitespace-nowrap" style="width: 50%">Editando registro</h1>
					@else
						<h1 class="text-lg text-gray-900 border-b border-gray-300 pb-1 w-full text-center font-bold whitespace-nowrap" style="width: 50%">Nuevo registro</h1>
					@endif
				</div>
			</x-slot>
	
			<x-slot name="content">
				<div class="mx-6 mt-4">
					<x-input-label>DNI/RUC</x-input-label>
					<x-text-input class="h-10 text-sm w-full" wire:model="customer.dni_ruc"></x-text-input>
					@error('customer.dni_ruc') <span class="text-red-600"> {{ $message }} </span> @enderror
				</div> 
				
				<div class="grid grid-cols-2 mx-6 mb-4 gap-2">
					<!-- COLUMNA 1 -->
					<div>
						<div class="mt-2">
							<x-input-label class="mb-1">Nombre</x-input-label>
							<x-text-input class="h-10 text-sm w-full" wire:model="customer.name"></x-text-input>
							@error('customer.name') <span class="text-red-600"> {{ $message }} </span> @enderror
						</div>

						<div class="mt-2">
							<x-input-label class="mb-1">Correo electrónico</x-input-label>
							<x-text-input class="h-10 text-sm w-full" wire:model="customer.email"></x-text-input>
							@error('customer.email') <span class="text-red-600"> {{ $message }} </span> @enderror
						</div>

						<div class="mt-2">
							<x-input-label class="mb-1">Tipo de cliente</x-input-label>
							<select wire:model="customer.customer_type_id" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
								@foreach($customer_types as $customerType)
									<option value="{{ $customerType->id }}">{{ $customerType->name }}</option>
								@endforeach
							</select>
							@error('customer.customer_type_id') <span class="text-red-600"> {{ $message }} </span> @enderror
						</div>
					</div>

					<!-- COLUMNA 2 -->
					<div>
						<div class="mt-2">
							<x-input-label class="mb-1">Teléfono</x-input-label>
							<x-text-input class="h-10 text-sm w-full" wire:model="customer.phone"></x-text-input>
							@error('customer.phone') <span class="text-red-600"> {{ $message }} </span> @enderror
						</div>

						<div class="mt-2">
							<x-input-label class="mb-1">Dirección</x-input-label>
							<x-text-input class="h-10 text-sm w-full" wire:model="customer.address"></x-text-input>
							@error('customer.address') <span class="text-red-600"> {{ $message }} </span> @enderror
						</div>

						<div class="mt-2">
							<x-input-label class="mb-1">Ciudad</x-input-label>
							<select wire:model="customer.city" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" class="dropdown" required>
								@foreach($cities as $city)
									<option value="{{ $city }}">{{ $city }}</option>
								@endforeach
							</select>
							@error('customer.city') <span class="text-red-600"> {{ $message }} </span> @enderror
						</div>
					</div>
				</div>
			</x-slot>
	
			<x-slot name="footer">
				<div class="py-2 w-auto items-end justify-center">
					<x-primary-button wire:click="$dispatch('close-modal', 'modal-form-customer')" class="h-10 bg-red-600 hover:bg-red-500 mr-2">Cancelar</x-primary-button>
				</div>
				<div class="py-2 w-auto items-end justify-center">
					<x-primary-button type="submit" class="h-10 shadow-sm">Aceptar</x-primary-button>
				</div>
			</x-slot>
		</x-dialog-modal>
	</form>	

	@script
	<script>
		$wire.on('deleteCustomer', (id) => {
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
