<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg m-2 h-[calc(100vh-64px)]"> <!-- CONTENEDOR MAESTRO -->
	<!-- Vista Superior -->
	<div class="flex items-center w-full my-2">
		<h2 class="text-xl font-semibold my-1 ml-6 text-gray-900 ms- w-full">Panel de control: Facturación</h2>
	</div>

	<div class="border-t border-gray-300"></div> <!-- Separador -->

	<!-- Vista de Facturación -->
	<div class="flex flex-col p-2 w-full h-[calc(100vh-120px)] overflow-y-scroll">
		<!-- INFO DEL CLIENTE -->
		<div class="flex-col content-center px-2 py-1 m-1 bg-gray-100 border border-gray-300 rounded-md shadow-md">
			<x-input-label class="ms-1">Datos de cliente</x-input-label>
			<div class="flex mb-1">
				<x-text-input class="text-sm h-7 px-2 mr-2" wire:model.live="cDniRuc" placeholder="DNI/RUC" readonly disabled style="width: 160px;"/>
				<x-text-input class="text-sm h-7 px-2 mr-2" wire:model.live="cName" placeholder="Nombre" readonly disabled style="width: 200px;"/>
				<x-text-input class="text-sm h-7 px-2 mr-2" wire:model.live="cEmail" placeholder="Correo electrónico" readonly disabled style="width: 180px;"/>
				<x-text-input class="text-sm h-7 px-2 mr-2" wire:model.live="cPhone" placeholder="Teléfono" readonly disabled style="width: 100px;"/>
				<x-text-input class="text-sm h-7 px-2 mr-2" wire:model.live="cAddress" placeholder="Dirección" readonly disabled style="width: 200px;"/>
				<x-text-input class="text-sm h-7 px-2 mr-2" wire:model.live="cCity" placeholder="Ciudad" readonly disabled style="width: 132px;"/>
				<x-text-input class="text-sm h-7 px-2 mr-2" wire:model.live="cType" placeholder="Tipo de cliente" readonly disabled style="width: 200px;"/>
				<x-primary-button class="place-items-end bg-gray-900 rounded-3xl z-10 px-0 h-7" wire:click="openCustomerSelection">
					<div class="flex items-center mx-2">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
							<path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 1 0 0 13.5 6.75 6.75 0 0 0 0-13.5ZM2.25 10.5a8.25 8.25 0 1 1 14.59 5.28l4.69 4.69a.75.75 0 1 1-1.06 1.06l-4.69-4.69A8.25 8.25 0 0 1 2.25 10.5Z" clip-rule="evenodd" />
						</svg>
						<span class="text-md whitespace-nowrap text-white ms-2">Buscar</span>
					</div>
				</x-primary-button>
			</div>
		</div>
		
		<!-- PANEL DE PRODUCTOS AGREGADOS Y VISTA PREVIA DEL PRODUCTO -->
		<div class="flex-grow flex flex-row content-center px-2 py-2 m-1 bg-gray-100 border border-gray-300 rounded-md shadow-md">
			<div class="flex min-w-[calc(100%-24rem)] max-h-[calc(100vh-9rem)] bg-white border border-gray-300 overflow-y-scroll me-2" >
				<!-- TABLA DE FACTURACION --> 
				<table class="min-w-full bg-white h-fit" >
					<thead>
						<tr class="h-6">
							<th class="px-2 border border-gray-300 text-center text-sm font-semibold w-4">Código</th>
							<th class="px-2 border border-gray-300 text-center text-sm font-semibold">Producto</th>
							<th class="px-2 border border-gray-300 text-center text-sm font-semibold w-8">Precio</th>
							<th class="px-2 border border-gray-300 text-center text-sm font-semibold w-4">Cantidad</th>
							<th class="px-2 border border-gray-300 text-center text-sm font-semibold w-8">Subtotal</th>
							<th class="px-2 border border-gray-300 text-center text-sm font-semibold w-5">Acciones</th>
						</tr>
					</thead>
					<tbody>
						@if($invoiceTable !== null)
							@foreach($invoiceTable as $item)
								<tr class="h-6 hover:bg-gray-100">
									<td class="h-6 px-2 border border-gray-300 text-center text-sm text-gray-700 truncate">{{ $item['code'] }}</td>
									<td class="h-6 px-2 border border-gray-300 text-left text-sm text-gray-700 truncate">{{ $item['name'] }}</td>
									<td class="h-6 px-2 border border-gray-300 text-right text-sm text-gray-700 truncate">${{ \App\Support\Decimal::format($item['price'], 2) }}</td>
									<td class="h-6 px-2 border border-gray-300 text-right text-sm text-gray-700 truncate">{{ $item['quantity'] }}</td>
									<td class="h-6 px-2 border border-gray-300 text-right text-sm text-gray-700 truncate">${{ \App\Support\Decimal::format($item['subtotal'], 2) }}</td>
									<td class="h-6 px-2 border py-1 text-center text-sm text-gray-700 truncate">
										<button class="h-6 bg-red-600 hover:bg-orange-400 text-white hover:text-black font-black px-2 rounded-3xl" wire:click="removeFromInvoice({{ $item['id'] }})">Eliminar</button>
									</td>
								</tr>
							@endforeach
						@endif
					</tbody>
				</table>
			</div>

			<div class="flex-col w-96 justify-center items-center ms-2">
				<!-- VISUALIZACION DE PRODUCTO --> 
				@if($selectedProduct != null)
					<div class="border rounded-lg px-4 py-2 shadow flex flex-col justify-between bg-white w-full">
						<div>
							@if ($selectedProduct->image_path === null || !Storage::disk('public')->exists($selectedProduct->image_path))
								<img src="{{ asset('storage/productImages/_default.jpg') }}" class="w-full h-40 my-2 object-scale-down">
							@else
								<img src="{{ asset('storage/' . $selectedProduct->image_path) }}" class="w-full h-40 my-2 object-scale-down">
							@endif
							<div class="border-t border-gray-300 my-2"></div> <!-- Separador -->
							<div class="w-full text-center text-lg font-bold">{{ $selectedProduct->name }}</div>
							<div class="border-t border-gray-300 my-2"></div> <!-- Separador -->
						</div>
	
						<div class="flex flex-col mt-auto">
							<div class="flex flex-col items-baseline">
								<div class="w-full text-sm">Código: {{ $selectedProduct->code }}</div>
								<div class="w-full text-sm">Modelo: {{ $selectedProduct->model }}</div>
								<div class="w-full text-sm">Marca: {{ $selectedProduct->brand }}</div>
				
								<div class="w-full text-sm text-end">Stock: <span class="font-black">{{ $selectedProduct->stock }}</span></div>
								<div class="w-full text-md text-center"><span class="font-black">$ {{ $selectedProduct->price }}</span></div>
							</div>
						</div>
					</div>
					<!-- CONTROL DE CANTIDADES -->
					<div class="flex justify-end items-center my-2 ">
						<div>
							<span class="text-md m-2">Cantidad:</span>
						</div>
						<div class="mx-2">
							<input type="number" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9.]/g, '');" min="1" wire:model="productCount" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm h-8 w-20 px-2"/>
						</div>
						<div>
							<x-primary-button class="px-0 h-8" wire:click="addToInvoice({{ $selectedProduct->id }})">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 ms-2">
									<path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
								</svg>
								<span class="text-md whitespace-nowrap text-white mx-2">Agregar</span>
							</x-primary-button>
						</div>
					</div>
				@endif
			</div> 
		</div>
		
		<!-- AREA DE BUSQUEDA h-36 --> 
		<div class="h-60 flex flex-row content-center px-2 py-1 m-1 bg-gray-100 border border-gray-300 rounded-md shadow-md">
			<div class="flex-col flex-grow justify-center items-center">
				<!-- PANEL DE BUSQUEDA --> 
				<div class="flex items-center space-x-2 mb-1">
					<x-input-label>Filtrar por:</x-input-label>
					<x-select class="text-sm h-7 px-2 mr-2 p-0" style="width: 150px" wire:model.live="searchMode">
						@foreach($fields as $field)
							<option class="text-sm" value="{{ $field }}">{{ $field }}</option>
						@endforeach
					</x-select>
					<x-text-input class="text-sm h-7 px-2 mr-2" placeholder="Buscar" style="width: 350px" wire:model.live="searching"></x-text-input>
				</div>
				
				<!-- TABLA DE PRODUCTOS h-24-->
				<div class="flex-grow"> 
					<div class="h-48 bg-white border border-gray-300 overflow-y-scroll me-2">
						<table class="table-fixed min-w-full bg-white">
							<thead>
								<tr>
									<th class="px-2 border border-gray-300 text-center text-sm font-semibold w-4">{{ __('Codigo') }}</th>
									<th class="px-2 border border-gray-300 text-center text-sm font-semibold">{{ __('Nombre') }}</th>
									<th class="px-2 border border-gray-300 text-center text-sm font-semibold">{{ __('Marca') }}</th>
									<th class="px-2 border border-gray-300 text-center text-sm font-semibold">{{ __('Proveedor') }}</th>
									<th class="px-2 border border-gray-300 text-center text-sm font-semibold">{{ __('Modelo') }}</th>
									<th class="px-2 border border-gray-300 text-center text-sm font-semibold">{{ __('Categoría') }}</th>
									<th class="px-2 border border-gray-300 text-center text-sm font-semibold">{{ __('Stock') }}</th>
									<th class="px-2 border border-gray-300 text-center text-sm font-semibold">{{ __('Precio') }}</th>
								</tr>
							</thead>
				
							<tbody>
								@foreach($products as $product)
									<tr class="hover:bg-blue-400 hover:cursor-pointer" wire:click="rowSelect({{ $product->id }})">
										<td class="h-6 px-2 border border-gray-300 text-center text-sm text-gray-700 truncate" >{{ $product->code}}</td>
										<td class="h-6 px-2 border border-gray-300 text-left text-sm text-gray-700 truncate">{{ $product->name }}</td>
										<td class="h-6 px-2 border border-gray-300 text-left text-sm text-gray-700 truncate">{{ $product->brand }}</td>
										<td class="h-6 px-2 border border-gray-300 text-left text-sm text-gray-700 truncate">{{ $this->getSupplierName($product->supplier_id) }}</td>
										<td class="h-6 px-2 border border-gray-300 text-left text-sm text-gray-700 truncate">{{ $product->model }}</td>
										<td class="h-6 px-2 border border-gray-300 text-left text-sm text-gray-700 truncate">{{ $this->getCategoryName($product->category_id) }}</td>
										<td class="h-6 px-2 border border-gray-300 text-right text-sm text-gray-700 truncate">
											@php
												// Buscar si el producto está en la tabla de factura
												$inInvoice = collect($invoiceTable)->firstWhere('id', $product->id);
												$stockRemaining = $product->stock - ($inInvoice['quantity'] ?? 0);
											@endphp

											@if($stockRemaining === 0)
												<span class="text-red-600 font-black">NO STOCK</span>
											@else
												{{ $stockRemaining }}
											@endif
										</td>
										<td class="h-6 px-2 border border-gray-300 text-right text-sm text-gray-700 truncate">${{ $product->price }}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div> 
			<div class="flex w-96 justify-center items-center px-4">
				<div class="flex-col justify-center w-full h-full"> 
					<!-- TOTAL FACTURA --> 
					
					<div class="flex flex-col p-1 justify-center items-center"> 
						<span class="text-lg font-semibold" >Total</span>
						<div class="flex text-xl bg-gray-950 rounded-lg text-white font-black p-1 pe-3 mx-1 h-12 items-center justify-end" style="width: 150px">
							<span>$ {{ \App\Support\Decimal::format($this->totalFinal, 2) }}</span>
						</div>
					</div>

					@if ($this->paymentAffectsCash)
						<div class="flex w-full justify-center">
							<div class="flex-col p-1 justify-center items-center">
								<x-input-label class="ms-2">Importe recibido</x-input-label>
								<x-input-numeric inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9.]/g, '');" wire:model.live="amount" class="text-xl rounded-lg border-none font-black p-1 pe-3 mx-1 bg-gray-950 text-white text-right" style="width: 150px"></x-input-numeric>
							</div>
		
							<div class="flex-col p-1 justify-center items-center">
								<x-input-label class="ms-2">Cambio</x-input-label>
								<div class="text-xl bg-gray-950 rounded-lg text-white font-black p-1 pe-3 text-right mx-1" style="width: 150px">
                                                                        <span>@if (\App\Support\Decimal::compare($this->change, '0.00') !== 0) $ {{ \App\Support\Decimal::format($this->change) }} @else --- @endif</span>
								</div>
							</div>
						</div>
					@else
						<div class="flex w-full justify-center">
							<div class="flex-col p-1 justify-center items-center">
								<x-input-label class="ms-2">Importe recibido</x-input-label>
								<div class="text-xl bg-neutral-500 rounded-lg text-white font-black p-1 pe-3 text-right mx-1 h-9" style="width: 120px"></div>
							</div>
		
							<div class="flex-col p-1 justify-center items-center">
								<x-input-label class="ms-2">Cambio</x-input-label>
								<div class="text-xl bg-neutral-500 rounded-lg text-white font-black p-1 pe-3 text-right mx-1 h-9" style="width: 120px"></div>
							</div>
						</div>
					@endif

					<div class="flex m-1 justify-center">
						<div class="flex items-center mx-1 font-medium">
							Método de pago: 
						</div>
						@foreach ($this->paymentMethods as $method)
							<div class="flex items-center mx-1">
								<input class="focus:ring-transparent hover:cursor-pointer mr-2" type="radio" name="paymentMethod" value="{{ $method->id }}" wire:model.live="paymentMethod">
								<label class="text-sm text-gray-950 ms-1">{{ $method->name }}</label>
							</div>
						@endforeach
					</div>
					
					<div class="border-t border-gray-300 "></div> <!-- Separador -->
					
					<!-- BOTONES DE ACCION  -->
					<div class="flex space-x-2 justify-center p-2">
						<a wire:click="clearInvoice" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-md h-10 hover:cursor-pointer">
							Cancelar
						</a>
                                                @if (count($this->invoiceTable) == 0 || $this->cId == null || ! $this->hasSufficientPayment)
							<a class="px-4 py-2 bg-neutral-400 text-white font-bold rounded-md h-10 hover:cursor-pointer" disabled>
								Procesar
							</a> 
						@else
							<a class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-md h-10 hover:cursor-pointer" wire:click.prevent="saveInvoice">
								Procesar
							</a> 
						@endif
					</div> 
				</div>
			</div>
		</div>
	</div>

	{{-- FORMULARIO DE SELECCION DE CIENTE MODAL --}}
	<x-full-dialog-modal name="modal-list-customers" fullScreen="true">
		<x-slot name="title">
			<div class="flex justify-center mt-2">
				<h1 class="text-lg text-gray-900 border-b border-gray-300 pb-1 w-full text-center font-bold whitespace-nowrap" style="width: 50%">Clientes registrados</h1>
			</div>
		</x-slot>

		<x-slot name="content">
			<div class="m-6 bg-white border border-gray-300 rounded-lg shadow-md overflow-y-scroll" style="height: 520px">
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
							<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white w-10">{{ __('Acciones') }}</th>
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
		
								<!-- BOTONES DE ACCION -->
								<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700">
									<div class="flex w-full justify-center">
										<a class="z-30 cursor-pointer" wire:click="selectCustomer({{ $customer->id }})">
											<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6" class="fill-gray-900 hover:fill-green-500">
												<path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
											</svg>
										</a>
									</div>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</x-slot>

		<x-slot name="footer">
			<div class="py-2 w-auto items-end justify-end">
				<x-primary-button wire:click="$dispatch('close-modal', 'modal-list-customers')" class="h-10 bg-red-600 hover:bg-red-500 mr-2">Cancelar</x-primary-button>
			</div>
		</x-slot>
	</x-full-dialog-modal>

	@script
	<script>
		$wire.on('noStock', () => {
			Swal.fire({
				title: 'No hay suficiente stock para este producto',
				icon: "error",
				draggable: true
			});
		});

		$wire.on('downloadInvoice', (url) => {
			Swal.fire({
				title: "Factura Generada",
				text: "¿Desea imprimir la factura?",
				icon: "question",
				showCancelButton: true,
				confirmButtonColor: "#16a34a",
				cancelButtonColor: "#dc2626",
				confirmButtonText: "¡Sí, imprimir!",
				cancelButtonText: 'Omitir'
			}).then((result) => {
				if (result.isConfirmed) {
					window.open(url, '_blank');
				}
			});
		});
	</script>
	
	@endscript
</div>
