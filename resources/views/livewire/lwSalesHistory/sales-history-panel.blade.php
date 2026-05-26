<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg m-2 h-[calc(100vh-64px)]">
	<!-- CONTENEDOR MAESTRO -->

	<!-- Vista Superior -->
	<div class="flex items-center w-full my-3">
		<h2 class="text-xl font-semibold my-2 ml-6 text-gray-900 w-full">Panel de ventas: Historial de ventas</h2>
	</div>

	<div class="border-t border-gray-300"></div> <!-- Separador -->

	<!-- Tabla de datos -->
	<div class="m-6 bg-white border border-gray-300 rounded-lg shadow-md overflow-hidden">
		<table class="min-w-full bg-white border-collapse p-6">
			<thead>
				<tr>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white w-10 whitespace-nowrap">
						<button wire:click="sortBy('id')" class="flex items-center justify-center w-full text-white">
							{{ __('No. Venta') }}
							@if ($sortColumn === 'id')
								<span class="ml-2">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
							@endif
						</button>
					</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">
						<button wire:click="sortBy('customer_id')" class="flex items-center justify-center w-full text-white">
							{{ __('Cliente') }}
							@if ($sortColumn === 'customer_id')
								<span class="ml-2">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
							@endif
						</button>
					</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">
						<button wire:click="sortBy('user_id')" class="flex items-center justify-center w-full text-white">
							{{ __('Vendedor') }}
							@if ($sortColumn === 'user_id')
								<span class="ml-2">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
							@endif
						</button>
					</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">
						<button wire:click="sortBy('total')" class="flex items-center justify-center w-full text-white">
							{{ __('Total') }}
							@if ($sortColumn === 'total')
								<span class="ml-2">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
							@endif
						</button>
					</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">
						<button wire:click="sortBy('sale_date')" class="flex items-center justify-center w-full text-white">
							{{ __('Fecha') }}
							@if ($sortColumn === 'sale_date')
								<span class="ml-2">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
							@endif
						</button>
					</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white w-10 whitespace-nowrap">{{ __('Metodo de pago') }}</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white w-10">{{ __('Acciones') }}</th>
				</tr>
			</thead>

			<tbody>
				@foreach($sales as $sale)
					<tr class="hover:bg-gray-100">
						<td class="py-2 px-4 border-b border-gray-300 text-center text-sm text-gray-700">{{ $sale->id }}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700">{{ $sale->customer->name }}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700">{{ $sale->user->name }}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-right text-sm text-gray-700">${{ number_format($sale->total, 2) }}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-center text-sm text-gray-700">{{ $sale->sale_date }}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-center text-xs font-black uppercase">
							<div class="flex justify-center items-center">
								<div class="px-1 rounded-3xl border w-fit {{ $sale->paymentMethod->id === 1 ? 'bg-green-200 border-green-600 text-green-600' : 'bg-blue-200 border-blue-600 text-blue-600' }}">
									{{ $sale->paymentMethod->name }}
								</div>
							</div>
						</td>

						<!-- BOTONES DE ACCION -->
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700">
							<div class="flex w-full justify-center">
								<a class="z-30 cursor-pointer" wire:click="view({{ $sale->id }})">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 mx-2 fill-gray-700 hover:fill-sky-500">
										<path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
										<path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 0 1 0-1.113ZM17.25 12a5.25 5.25 0 1 1-10.5 0 5.25 5.25 0 0 1 10.5 0Z" clip-rule="evenodd" />
									</svg>
								</a>
								<a class="z-30 cursor-pointer" wire:click="$dispatch('deleteSale', { id: {{ $sale->id }} })">
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

	<div class="my-2">
		{{ $sales->links('custom-tailwind') }}
	</div>

	<!-- Modal para los detalles de la venta -->
	<x-dialog-modal name="modal-sale-detail" maxWidth="3xl">
		<x-slot name="title">
			<div class="flex justify-center mt-2">
				<h1 class="text-lg text-gray-900 border-b border-gray-300 pb-1 w-full text-center font-bold whitespace-nowrap" style="width: 50%">Detalle de venta</h1>
			</div>
		</x-slot>

		<x-slot name="content">
			<div class="px-8 py-2"> 
				@if($selectedSale)
				<div class="mb-4">
					<div class="border-t border-gray-300 mt-4"></div> <!-- Separador -->
					
					<!-- INFORMACION DEL CLIENTE -->
					<p class="text-base mb-2"><strong>Información del cliente</strong></p>
					<div class="flex">
						<div class="flex-col me-10">
							<div><span><strong>DNI/RUC</strong></span></div>
							<div><span>{{ $selectedSale->customer->dni_ruc }}</span></div>
						</div>
						<div class="flex-col me-10">
							<div><span><strong>Nombre</strong></span></div>
							<div><span>{{ $selectedSale->customer->name }}</span></div>
						</div>
						<div class="flex-col me-10">
							<div><span><strong>Correo</strong></span></div>
							<div><span>{{ $selectedSale->customer->email }}</span></div>
						</div>
						<div class="flex-col me-10">
							<div><span><strong>Telefono</strong></span></div>
							<div><span>{{ $selectedSale->customer->phone }}</span></div>
						</div>
					</div>

					<div class="border-t border-gray-300 mt-4"></div> <!-- Separador -->

					<!-- INFORMACION DEL USUARIO -->
					<p class="text-base mb-2"><strong>Información del vendedor</strong></p>
					<div class="flex">
						<div class="flex-col me-20">
							<div><span><strong>Nombre</strong></span></div>
							<div><span>{{ $selectedSale->user->name }}</span></div>
						</div>
						<div class="flex-col me-10">
							<div><span><strong>Correo</strong></span></div>
							<div><span>{{ $selectedSale->user->email }}</span></div>
						</div>
					</div>

					<div class="border-t border-gray-300 mt-4"></div> <!-- Separador -->

					<div class="flex items-center">
						<div class="w-full">
							<p><strong>Fecha:</strong> {{ $selectedSale->sale_date }}</p>
						</div>
						<div class="text-end">
							<p><strong>Metodo de pago:</strong> {{ $selectedSale->paymentMethod->name }}</p>
						</div>
					</div>
				</div>

				<table class="min-w-full bg-white border border-gray-300">
					<thead>
						<tr>
							<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">Producto</th>
							<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">Cantidad</th>
							<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">Precio</th>
							<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">Subtotal</th>
						</tr>
					</thead>
					<tbody>
						@foreach($saleDetails as $detail)
							<tr>
								<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700">{{ $detail->product->name }}</td>
								<td class="py-2 px-4 border-b border-gray-300 text-right text-sm text-gray-700">{{ $detail->quantity }}</td>
								<td class="py-2 px-4 border-b border-gray-300 text-right text-sm text-gray-700">${{ number_format($detail->price, 2) }}</td>
								<td class="py-2 px-4 border-b border-gray-300 text-right text-sm text-gray-700">${{ number_format($detail->total, 2) }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>

				<div class="text-right text-lg font-black px-4 mt-2">
					<span>Total: ${{ number_format($selectedSale->total, 2) }}</span>
				</div>
			</div>
			@endif
		</x-slot>

		<x-slot name="footer">
			<div class="flex p-1 w-full justify-end items-center">
				<img src="{{ asset('graphicResources/LEO AutoParts LINE BLACK.png') }}" class="w-fit h-5 object-scale-down">
			</div>
		</x-slot>
	</x-dialog-modal>

	@script
	<script>
		$wire.on('deleteSale', (id) => {
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
