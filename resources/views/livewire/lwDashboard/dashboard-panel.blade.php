<div class="bg-white overflow-y-scroll shadow-sm sm:rounded-lg m-2 h-[calc(100vh-4rem)]"> <!-- CONTENEDOR MAESTRO -->
	<!-- Vista Superior -->
	<div class="flex items-center w-full my-3">
		<h2 class="text-xl font-semibold my-2 ml-6 text-gray-900 ms- w-full">Panel de control: Dashboard</h2>
	</div>

	<div class="border-t border-gray-300"></div> <!-- Separador -->

	<div class="p-6 h-[calc(100vh-133px)] flex flex-col">
		<div class="flex w-full justify-end mb-4 px-2">
			<x-primary-button wire:click="$dispatch('alerta')">SWEET ALERT</x-primary-button>
		</div>

		<!-- Tarjetas de Métricas -->
		<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
			<div class="bg-blue-200 p-6 rounded-lg shadow border-2 border-blue-600 relative overflow-hidden">
				<h3 class="text-lg font-semibold text-gray-700">Total Ventas</h3>
				<p class="text-3xl font-bold text-blue-600">{{ $totalSales }}</p>

				<svg class="absolute top-1 -right-3 text-white opacity-75 w-32 h-32" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M6 2L3 6v14c0 1.1.9 2 2 2h14a2 2 0 0 0 2-2V6l-3-4H6zM3.8 6h16.4M16 10a4 4 0 1 1-8 0"/>
				</svg>
			</div>
			<div class="bg-green-300 border-2 border-green-600 p-6 rounded-lg shadow relative overflow-hidden">
				<h3 class="text-lg font-semibold text-gray-700">Clientes Registrados</h3>
				<p class="text-3xl font-bold text-green-600">{{ $totalCustomers }}</p>

				<svg class="absolute top-1 -right-3 text-white opacity-75 w-32 h-32" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
				</svg>
			</div>
	
			<div class="bg-red-300 border-2 border-red-600 p-6 rounded-lg shadow relative overflow-hidden">
				<h3 class="text-lg font-semibold text-gray-700">Productos Disponibles</h3>
				<p class="text-3xl font-bold text-red-600">{{ $totalProducts }}</p>

				<svg class="absolute top-1 -right-3 text-white opacity-75 w-32 h-32" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M21 12H3M12 3v18"/>
				</svg>
			</div>
		</div>

		<div class="border-t border-gray-300 my-4"></div> <!-- Separador -->

		<div class="flex flex-wrap justify-around p-4">
			<div class="flex flex-wrap justify-around gap-6 p-4">
				<!-- Gráfico de Barras Verticales -->
				<div class="flex flex-col items-center bg-white p-4 rounded-lg shadow w-auto border border-gray-800">
					<h3 class="text-lg font-semibold text-gray-700 mb-2">Ventas por Mes</h3>
					<div class="flex items-end space-x-2 h-40 p-4 bg-gray-100 rounded-lg">
						@foreach ($ventasPorMes as $mes => $total)
							<div class="w-10 bg-blue-900 rounded-t" style="height: {{ min(100, ($total / max($ventasPorMes)) * 100) }}%;"></div>
						@endforeach
					</div>
				</div>
			
				<!-- Gráfico de Barras Horizontales -->
				<div class="bg-white p-4 rounded-lg shadow w-auto border border-gray-800">
					<h3 class="text-lg font-semibold text-gray-700 mb-2">Top 3 Vendedores</h3>
					<div class="space-y-3">
						@foreach ($topSellers as $seller)
							<div>
								<p class="text-sm font-semibold">{{ $seller->user->name }}</p>
								<div class="w-full bg-gray-200 rounded h-4">
									<div class="bg-blue-900 h-4 rounded" style="width: {{ min(100, ($seller->sales_count / max($topSellers->pluck('sales_count')->toArray())) * 100) }}%;"></div>
								</div>
							</div>
						@endforeach
					</div>
				</div>
			
				<!-- Círculo de Progreso -->
				<div class="flex flex-col items-center bg-white p-4 rounded-lg shadow w-auto border border-gray-800">
					<h3 class="text-lg font-semibold text-gray-700 mb-2">Métodos de Pago</h3>
					<div class="relative w-24 h-24">
						<div class="w-24 h-24 rounded-full bg-gray-200 relative">
							<div class="absolute inset-0 w-full h-full rounded-full"
								style="background: conic-gradient(#3b82f6 0% {{ $paymentComparison['paypal'] }}%, #10b981 {{ $paymentComparison['paypal'] }}% 100%);">
							</div>
						</div>
					</div>
					<div class="flex gap-2 my-2">
						<div class="flex-col justify-center">
							<div class="px-1 rounded-3xl w-full text-center text-black font-black">
								{{ $paymentComparison['cash'] }}%
							</div>
							<div class="px-1 rounded-3xl border w-full text-center bg-green-200 border-green-600 text-green-600 font-black">
								Efectivo
							</div>
						</div>
						<div class="flex-col justify-center">
							<div class="px-1 rounded-3xl w-full text-center text-black font-black">
								{{ $paymentComparison['paypal'] }}%
							</div>
							<div class="px-1 rounded-3xl border w-full text-center bg-blue-200 border-blue-600 text-blue-600 font-black">
								PayPal
							</div>
						</div>
					</div>

				</div>
				
			</div>

			<!-- Últimas Ventas -->
		<div class="bg-white p-6 rounded-lg shadow border border-gray-900">
			<h3 class="text-lg font-semibold text-gray-700 mb-4">Últimas Ventas</h3>
			<table class="w-full text-left border-collapse">
				<thead>
					<tr class="bg-gray-200">
						<th class="p-2">#</th>
						<th class="p-2">Cliente</th>
						<th class="p-2">Total</th>
						<th class="p-2">Fecha</th>
					</tr>
				</thead>
				<tbody>
					@foreach($recentSales as $sale)
						<tr class="border-b">
							<td class="p-2 text-center">{{ $sale->id }}</td>
							<td class="p-2">{{ $sale->customer->name }}</td>
							<td class="p-2 font-semibold text-blue-500 text-right">${{ number_format($sale->total, 2) }}</td>
							<td class="p-2">{{ $sale->sale_date }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
			
		</div>

		

		<div class="border-t border-gray-300 my-4"></div> <!-- Separador -->
	
		<!-- Accesos Rápidos -->
		<div class="mt-0 flex space-x-4 w-full justify-center pb-4">
			<a href="{{ route('invoicing') }}" class="px-6 font-black py-2 bg-blue-600 text-white rounded-3xl shadow">Nueva Venta</a>
			<a href="{{ route('customers') }}" class="px-6 font-black py-2 bg-green-500 text-white rounded-3xl shadow">Ver Clientes</a>
			<a href="{{ route('inventory') }}" class="px-6 font-black py-2 bg-red-600 text-white rounded-3xl shadow">Ver Productos</a>
		</div>

		{{-- <!-- Tarjetas de Métricas -->
		<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
			<div class="bg-blue-200 p-6 rounded-lg shadow border-2 border-blue-600 relative overflow-hidden">
				<h3 class="text-lg font-semibold text-gray-700">Total Ventas</h3>
				<p class="text-3xl font-bold text-blue-600">{{ $totalSales }}</p>

				<svg class="absolute top-1 -right-3 text-white opacity-75 w-32 h-32" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M6 2L3 6v14c0 1.1.9 2 2 2h14a2 2 0 0 0 2-2V6l-3-4H6zM3.8 6h16.4M16 10a4 4 0 1 1-8 0"/>
				</svg>
			</div>
			<div class="bg-green-300 border-2 border-green-600 p-6 rounded-lg shadow relative overflow-hidden">
				<h3 class="text-lg font-semibold text-gray-700">Clientes Registrados</h3>
				<p class="text-3xl font-bold text-green-600">{{ $totalCustomers }}</p>

				<svg class="absolute top-1 -right-3 text-white opacity-75 w-32 h-32" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
				</svg>
			</div>
	
			<div class="bg-red-300 border-2 border-red-600 p-6 rounded-lg shadow relative overflow-hidden">
				<h3 class="text-lg font-semibold text-gray-700">Productos Disponibles</h3>
				<p class="text-3xl font-bold text-red-600">{{ $totalProducts }}</p>

				<svg class="absolute top-1 -right-3 text-white opacity-75 w-32 h-32" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M21 12H3M12 3v18"/>
				</svg>
			</div>
		</div>

		<div class="border-t border-gray-300 my-4"></div> <!-- Separador -->

		<div class="flex flex-wrap justify-around gap-6 p-4">
			<div class="flex flex-wrap justify-around gap-6 p-4">
				<!-- Gráfico de Barras Verticales -->
				<div class="flex flex-col items-center bg-white p-4 rounded-lg shadow w-auto">
					<h3 class="text-lg font-semibold text-gray-700 mb-2">Ventas por Mes</h3>
					<div class="flex items-end space-x-2 h-40 p-4 bg-gray-100 rounded-lg">
						@foreach ($ventasPorMes as $mes => $total)
							<div class="w-10 bg-blue-500 rounded-t" style="height: {{ min(100, ($total / max($ventasPorMes)) * 100) }}%;"></div>
						@endforeach
					</div>
				</div>
			
				<!-- Gráfico de Barras Horizontales -->
				<div class="bg-white p-4 rounded-lg shadow w-auto">
					<h3 class="text-lg font-semibold text-gray-700 mb-2">Comparación Mensual</h3>
					<div class="space-y-3">
						@foreach ($ventasPorMes as $mes => $total)
							<div>
								<p class="text-sm font-semibold">Mes {{ $mes }}</p>
								<div class="w-full bg-gray-200 rounded h-4">
									<div class="bg-green-500 h-4 rounded" style="width: {{ min(100, ($total / max($ventasPorMes)) * 100) }}%;"></div>
								</div>
							</div>
						@endforeach
					</div>
				</div>
			
				<!-- Círculo de Progreso -->
				<div class="flex flex-col items-center bg-white p-4 rounded-lg shadow w-auto">
					<h3 class="text-lg font-semibold text-gray-700 mb-2">Progreso de Ventas</h3>
					<div class="relative w-24 h-24">
						<div class="absolute inset-0 flex items-center justify-center text-xl font-bold text-blue-500">
							{{ round($progresoVentas) }}%
						</div>
						<div class="w-24 h-24 rounded-full bg-gray-200 relative">
							<div class="absolute inset-0 w-full h-full rounded-full"
								style="background: conic-gradient(#3b82f6 0% {{ $progresoVentas }}%, #e5e7eb {{ $progresoVentas }}% 100%);">
							</div>
						</div>
					</div>
				</div>
			</div>
			
		</div>

		<!-- Últimas Ventas -->
		<div class="bg-white p-6 rounded-lg shadow border border-gray-900">
			<h3 class="text-lg font-semibold text-gray-700 mb-4">Últimas Ventas</h3>
			<table class="w-full text-left border-collapse">
				<thead>
					<tr class="bg-gray-200">
						<th class="p-2">#</th>
						<th class="p-2">Cliente</th>
						<th class="p-2">Total</th>
						<th class="p-2">Fecha</th>
					</tr>
				</thead>
				<tbody>
					@foreach($recentSales as $sale)
						<tr class="border-b">
							<td class="p-2">{{ $sale->id }}</td>
							<td class="p-2">{{ $sale->customer->name }}</td>
							<td class="p-2 font-semibold text-blue-500">${{ number_format($sale->total, 2) }}</td>
							<td class="p-2">{{ $sale->sale_date }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>

		<div class="border-t border-gray-300 my-4"></div> <!-- Separador -->
	
		<!-- Accesos Rápidos -->
		<div class="mt-0 flex space-x-4 w-full justify-center">
			<a href="{{ route('invoicing') }}" class="px-6 font-black py-2 bg-blue-600 text-white rounded-3xl shadow">Nueva Venta</a>
			<a href="{{ route('customers') }}" class="px-6 font-black py-2 bg-green-500 text-white rounded-3xl shadow">Ver Clientes</a>
			<a href="{{ route('inventory') }}" class="px-6 font-black py-2 bg-red-600 text-white rounded-3xl shadow">Ver Productos</a>
		</div> --}}

	</div>

	@script
	<script>
		$wire.on('alerta', () => {
			Swal.fire({
				title: "¡Bienvenido a LEO AutoParts!",
				text: " -- Desarrollado por JC Dávila -- ",
				imageUrl: "{{ asset('graphicResources/LEO AutoParts LOGO BLACK.png') }}",
				imageWidth: 400,
				imageHeight: 200,
				confirmButtonText: "¡Allá vamos!",
				confirmButtonColor: "#111827"
			});
		});
	</script>
	@endscript
</div>