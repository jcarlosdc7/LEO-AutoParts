<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg m-2 h-[calc(100vh-64px)]"> <!-- CONTENEDOR MAESTRO -->
	<!-- Vista Superior -->
	<div class="flex items-center w-full my-3">
		<h2 class="text-xl font-semibold my-2 ml-6 text-gray-900 ms- w-full">Panel de control: Inventario</h2>

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

	<div class="flex px-6 my-2 justify-between content-end items-center w-full">
		<!-- FILTRO POR CAMPO (BUSQUEDA EN TIEMPO REAL) -->
		<div class="flex items-center space-x-2">
			<x-input-label>Filtrar por:</x-input-label>
			<x-select class="text-sm h-8 px-2 mr-2 p-0" style="width: 150px" wire:model.live="searchMode">
				@foreach($fields as $field)
					<option class="text-sm" value="{{ $field }}">{{ $field }}</option>
				@endforeach
			</x-select>
			<x-text-input class="text-sm h-8 px-2 mr-2" placeholder="Buscar" style="width: 350px" wire:model.live="searching"></x-text-input>
		</div>
	
		<!-- CONTROL DE MODO DE VISTA -->
		<div class="flex items-center">
			<div class="bg-red-300 w-full"></div>
			@if($viewMode === 'card')
				<x-input-label class="mx-2 whitespace-nowrap">Modo de visualización: <span class="font-black">Catálogo</span></x-input-label>
				<button class="p-1 rounded-s-md border bg-gray-900 border-gray-300" wire:click="setCardMode" disabled>
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 fill-white">
						<path fill-rule="evenodd" d="M3 6a3 3 0 0 1 3-3h2.25a3 3 0 0 1 3 3v2.25a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V6Zm9.75 0a3 3 0 0 1 3-3H18a3 3 0 0 1 3 3v2.25a3 3 0 0 1-3 3h-2.25a3 3 0 0 1-3-3V6ZM3 15.75a3 3 0 0 1 3-3h2.25a3 3 0 0 1 3 3V18a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3v-2.25Zm9.75 0a3 3 0 0 1 3-3H18a3 3 0 0 1 3 3V18a3 3 0 0 1-3 3h-2.25a3 3 0 0 1-3-3v-2.25Z" clip-rule="evenodd"/>
					</svg>
				</button>
				<button class="p-1 rounded-e-md border border-gray-300 hover:bg-gray-200 cursor-pointer" wire:click="setListMode">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
						<path d="M5.625 3.75a2.625 2.625 0 1 0 0 5.25h12.75a2.625 2.625 0 0 0 0-5.25H5.625ZM3.75 11.25a.75.75 0 0 0 0 1.5h16.5a.75.75 0 0 0 0-1.5H3.75ZM3 15.75a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75ZM3.75 18.75a.75.75 0 0 0 0 1.5h16.5a.75.75 0 0 0 0-1.5H3.75Z"/>
					</svg>
				</button>
			@endif
	
			@if($viewMode === 'list')
				<x-input-label class="mx-2 whitespace-nowrap">Modo de visualización: <span class="font-black">Lista</span></x-input-label>
				<button class="p-1 rounded-s-md border border-gray-300 hover:bg-gray-200 cursor-pointer" wire:click="setCardMode">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
						<path fill-rule="evenodd" d="M3 6a3 3 0 0 1 3-3h2.25a3 3 0 0 1 3 3v2.25a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V6Zm9.75 0a3 3 0 0 1 3-3H18a3 3 0 0 1 3 3v2.25a3 3 0 0 1-3 3h-2.25a3 3 0 0 1-3-3V6ZM3 15.75a3 3 0 0 1 3-3h2.25a3 3 0 0 1 3 3V18a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3v-2.25Zm9.75 0a3 3 0 0 1 3-3H18a3 3 0 0 1 3 3V18a3 3 0 0 1-3 3h-2.25a3 3 0 0 1-3-3v-2.25Z" clip-rule="evenodd"/>
					</svg>
				</button>
				<button class="p-1 rounded-e-md border bg-gray-900 border-gray-300" wire:click="setListMode" disabled>
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 fill-white">
						<path d="M5.625 3.75a2.625 2.625 0 1 0 0 5.25h12.75a2.625 2.625 0 0 0 0-5.25H5.625ZM3.75 11.25a.75.75 0 0 0 0 1.5h16.5a.75.75 0 0 0 0-1.5H3.75ZM3 15.75a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75ZM3.75 18.75a.75.75 0 0 0 0 1.5h16.5a.75.75 0 0 0 0-1.5H3.75Z"/>
					</svg>
				</button>
			@endif
		</div>
	</div>
	
	<div class="border-t border-gray-300 mb-2"></div> <!-- Separador -->

	<!-- INFORMACION EN MODO LISTA -->
	@if($viewMode === 'list')
	<div class="mx-6 bg-white border border-gray-300 rounded-lg shadow-md overflow-x-auto">
		<table class="min-w-full bg-white border-collapse p-6">
			<thead>
				<tr>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">{{ __('Codigo') }}</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">{{ __('Nombre') }}</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">{{ __('Marca') }}</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">{{ __('Proveedor') }}</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">{{ __('Modelo') }}</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">{{ __('Categoría') }}</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">{{ __('Stock') }}</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white">{{ __('Precio') }}</th>
					<th class="py-2 px-4 border border-gray-300 text-center text-sm font-semibold bg-gray-900 text-white w-10">{{ __('Acciones') }}</th>
				</tr>
			</thead>

			<tbody>
				@foreach($products as $product)
					<tr class="hover:bg-gray-100">
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700 truncate">{{ $product->code}}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700 truncate" style="width: 200px">{{ $product->name }}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700 truncate">{{ $product->brand }}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700 truncate">{{ $this->getSupplierName($product->supplier_id) }}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700 truncate">{{ $product->model }}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700 truncate">{{ $this->getCategoryName($product->category_id) }}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-right text-sm text-gray-700 truncate">{{ $product->stock }}</td>
						<td class="py-2 px-4 border-b border-gray-300 text-right text-sm text-gray-700 truncate">${{ $product->price }}</td>

						<!-- BOTONES DE ACCION -->
						<td class="py-2 px-4 border-b border-gray-300 text-left text-sm text-gray-700">
							<div class="flex w-full justify-center">
								<a class="z-30 cursor-pointer" wire:click="view({{ $product->id }})">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 mx-2 fill-gray-700 hover:fill-sky-500">
										<path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
										<path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 0 1 0-1.113ZM17.25 12a5.25 5.25 0 1 1-10.5 0 5.25 5.25 0 0 1 10.5 0Z" clip-rule="evenodd" />
									</svg>
								</a>
								<a class="z-30 cursor-pointer" wire:click="update({{ $product->id }})">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 mx-2 fill-gray-700 hover:fill-yellow-500">
										<path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z" />
										<path d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z" />
									</svg>
								</a>
								<a class="z-30 cursor-pointer" wire:click="$dispatch('deleteProduct', { id: {{ $product->id }} })">
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
	@endif

	<!-- INFORMACION EN MODO CATALOGO -->
	@if($viewMode === 'card')
		<div class="px-4 py-2 grid grid-cols-5 gap-2 overflow-y-scroll h-[calc(100vh-260px)]">
			@foreach($products as $product)
				<div class="border rounded-lg px-4 py-2 shadow flex flex-col justify-between">
					<div>
						@if ($product->image_path === null || !Storage::disk('public')->exists($product->image_path))
							<img src="{{ asset('storage/productImages/_default.jpg') }}" class="w-full h-40 my-2 object-scale-down">
						@else
							<img src="{{ asset('storage/' . $product->image_path) }}" class="w-full h-40 my-2 object-scale-down">
						@endif

						<div class="border-t border-gray-300 my-2"></div> <!-- Separador -->
						<div class="w-full text-center text-lg font-bold">{{ $product->name }}</div>
						<div class="border-t border-gray-300 my-2"></div> <!-- Separador -->
					</div>

					<div class="flex flex-col mt-auto">
						<div class="flex flex-col items-baseline">
							<div class="w-full text-sm">Código: {{ $product->code }}</div>
							<div class="w-full text-sm">Modelo: {{ $product->model }}</div>
							<div class="w-full text-sm">Marca: {{ $product->brand }}</div>
			
							<div class="w-full text-sm text-end">Stock: <span class="font-black">{{ $product->stock }}</span></div>
							<div class="w-full text-md text-center"><span class="font-black">$ {{ $product->price }}</span></div>
						</div>
						<div class="border-t border-gray-300 my-2"></div> <!-- Separador -->
						<div class="flex w-full justify-center">
							<a class="z-30 cursor-pointer" wire:click="view({{ $product->id }})">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 mx-2 fill-gray-700 hover:fill-sky-500">
									<path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
									<path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 0 1 0-1.113ZM17.25 12a5.25 5.25 0 1 1-10.5 0 5.25 5.25 0 0 1 10.5 0Z" clip-rule="evenodd" />
								</svg>
							</a>
							<a class="z-30 cursor-pointer" wire:click="update({{ $product->id }})">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 mx-2 fill-gray-700 hover:fill-yellow-500">
									<path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z" />
									<path d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z" />
								</svg>
							</a>
							<a class="z-30 cursor-pointer" wire:click="$dispatch('deleteProduct', { id: {{ $product->id }} })">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 mx-2 fill-gray-700 hover:fill-red-600">
									<path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd" />
								</svg>
							</a>
						</div>
					</div>
				</div>
			@endforeach
		</div>
	@endif
	
	<div class="my-2 ">
		{{ $products->links('custom-tailwind') }}
	</div>

	<!-- Visor detallado -->
	<x-dialog-modal name="modal-view-product" maxWidth="md"> 
		<x-slot name="title">
			<div class="flex justify-center mt-2">
				<h1 class="text-lg text-gray-900 border-b border-gray-300 pb-1 w-full text-center font-bold whitespace-nowrap" style="width: 50%">Información de producto</h1>
			</div>
		</x-slot>

		<x-slot name="content">
			<div style="height: auto">
				@if ($this->product->image_path === null || !Storage::disk('public')->exists($this->product->image_path))
					<img src="{{ asset('storage/productImages/_default.jpg') }}" class="w-full h-40 my-2 object-scale-down">
				@else
					<img src="{{ asset('storage/' . $this->product->image_path) }}" class="w-full h-40 my-2 object-scale-down">
				@endif
				<div class="px-6 text-lg text-center font-black"><span>{{ $this->product->name }}</span></div>
				<div class="px-6 mb-2"><x-input-label>Descripción</x-input-label><span>{{ $this->product->description }}</span></div>
				<div class="px-6 mb-2"><x-input-label>Marca</x-input-label><span>{{ $this->product->brand }}</span></div>
				<div class="px-6 mb-2"><x-input-label>Modelo</x-input-label><span>{{ $this->product->model }}</span></div>
				<div class="px-6 mb-2"><x-input-label>Proveedor</x-input-label><span>{{ $this->product->supplier_id }}</span></div>
				<div class="px-6 mb-2"><x-input-label>Categoría</x-input-label><span>{{ $this->product->category_id }}</span></div>
				<div class="px-6 text-lg text-end font-black"><x-input-label>Stock</x-input-label>
					@if($this->product->stock < $this->product->min_stock)
						<span class="text-red-600">{{ $this->product->stock }}</span>
					@else
						<span>{{ $this->product->stock }}</span>
					@endif
				</div>
				<div class="px-6 mb-2 text-lg text-end font-black"><x-input-label>Precio</x-input-label><span>{{ $this->product->price }}</span></div>
			</div>
		</x-slot>

		<x-slot name="footer" class="px-2">
			<div class="flex p-1 w-full justify-end items-center">
				<img src="{{ asset('graphicResources/LEO AutoParts LINE BLACK.png') }}" class="w-fit h-5 object-scale-down">
			</div>
		</x-slot>
	</x-dialog-modal>

	<!-- Formulario Modal -->
	<form wire:submit="save">
		<x-dialog-modal name="modal-form-product" maxWidth="lg"> 
			
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
				<div class="flex gap-2 mx-6 mt-4">
					<!-- Código -->
					<div class="w-32">
						<x-input-label class="mb-1">Código</x-input-label>
						<x-text-input class="h-10 text-sm w-full" wire:model="product.code"></x-text-input>
						@error('product.code') <span class="text-red-600">{{ $message }}</span> @enderror
					</div>
			
					<!-- Nombre -->
					<div class="w-full">
						<x-input-label class="mb-1">Nombre</x-input-label>
						<x-text-input class="h-10 text-sm w-full" wire:model="product.name"></x-text-input>
						@error('product.name') <span class="text-red-600">{{ $message }}</span> @enderror
					</div>
				</div>

				<!-- Descripción -->
				<div class="mx-6 mt-2">
					<x-input-label class="mb-1">Descripción</x-input-label>
					<x-textarea class="h-32 text-sm w-full" wire:model="product.description"></x-textarea>
					@error('product.description') <span class="text-red-600">{{ $message }}</span> @enderror
				</div>

				<div class="grid grid-cols-2 gap-2 mx-6">
					<!-- Marca -->
					<div>
						<x-input-label class="mb-1">Marca</x-input-label>
						<x-text-input class="h-10 text-sm w-full" wire:model="product.brand"></x-text-input>
						@error('product.brand') <span class="text-red-600">{{ $message }}</span> @enderror
					</div>
			
					<!-- Modelo -->
					<div>
						<x-input-label class="mb-1">Modelo</x-input-label>
						<x-text-input class="h-10 text-sm w-full" wire:model="product.model"></x-text-input>
						@error('product.model') <span class="text-red-600">{{ $message }}</span> @enderror
					</div>
			
					<!-- Categoría -->
					<div>
						<x-input-label class="mb-1">Categoría</x-input-label>
						<x-select class="h-10 text-sm w-full border rounded" wire:model="product.category_id">
							@foreach($categories as $category)
								<option value="{{ $category->id }}">{{ $category->name }}</option>
							@endforeach
						</x-select>
						@error('product.category_id') <span class="text-red-600">{{ $message }}</span> @enderror
					</div>
			
					<!-- Proveedor -->
					<div>
						<x-input-label class="mb-1">Proveedor</x-input-label>
						<x-select class="h-10 text-sm w-full border rounded" wire:model="product.supplier_id">
							@foreach($suppliers as $supplier)
								<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
							@endforeach
						</x-select>
						@error('product.supplier_id') <span class="text-red-600">{{ $message }}</span> @enderror
					</div>
				</div>

				<div class="flex w-auto gap-2 mx-6 mt-2">
					<!-- Stock -->
					<div>
						<x-input-label class="mb-1">Stock</x-input-label>
						<x-text-input type="number" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9.]/g, '');" min="1" class="h-10 text-sm w-full" wire:model="product.stock"></x-text-input>
						@error('product.stock') <span class="text-red-600">{{ $message }}</span> @enderror
					</div>
			
					<!-- Min_Stock -->
					<div>
						<x-input-label class="mb-1">Stock mínimo</x-input-label>
						<x-text-input type="number" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9.]/g, '');" min="1" class="h-10 text-sm w-full" wire:model="product.min_stock"></x-text-input>
						@error('product.min_stock') <span class="text-red-600">{{ $message }}</span> @enderror
					</div>
			
					<!-- Precio -->
					<div>
						<x-input-label class="mb-1">Precio</x-input-label>
						<x-text-input inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9.]/g, '');" min="1" class="h-10 text-sm w-full" wire:model="product.price"></x-text-input>
						@error('product.price') <span class="text-red-600">{{ $message }}</span> @enderror
					</div>
				</div>

				<!-- Subir Imagen -->
				<div class="mx-6 my-2">
					<x-input-label class="mb-1">Subir Imagen</x-input-label>
					<input type="file" class="h-10 text-sm w-full" wire:model.live="newImagePath" wire:key="{{ $newImagePathKey }}">
					@error('newImagePath') <span class="text-red-600">{{ $message }}</span> @enderror
				</div>
			</x-slot>
	
			<x-slot name="footer">
				<div class="py-2 w-auto items-end justify-center">
					<x-primary-button wire:click="$dispatch('close-modal', 'modal-form-product')" class="h-10 bg-red-600 hover:bg-red-500 mr-2">Cancelar</x-primary-button>
				</div>
				<div class="py-2 w-auto items-end justify-center">
					<x-primary-button type="submit" class="h-10 shadow-sm">Aceptar</x-primary-button>
				</div>
			</x-slot>
		</x-dialog-modal>
	</form>	

	@script
	<script>
		$wire.on('deleteProduct', (id) => {
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