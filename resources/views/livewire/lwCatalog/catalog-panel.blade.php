<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg m-2 h-[calc(100vh-64px)]"> <!-- CONTENEDOR MAESTRO -->
	<!-- Vista Superior -->
	<div class="flex items-center w-full my-3">
		<h2 class="text-xl font-semibold my-2 ml-6 text-gray-900 ms- w-full text-center">Catalogo de tienda</h2>
	</div>

	<div class="border-t border-gray-300"></div> <!-- Separador -->

	<div class="px-4 py-2 grid grid-cols-5 gap-2 overflow-y-scroll h-[calc(100vh-200px)]">
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
		
						<div class="w-full text-md text-center"><span class="font-black">$ {{ $product->price }}</span></div>
					</div>
				</div>
			</div>
		@endforeach
	</div>

	<div class="my-2 ">
		{{ $products->links('custom-tailwind') }}
	</div>
</div>