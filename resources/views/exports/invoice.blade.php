<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Factura #{{ $sale->id }}</title>
	<style>
		body { font-family: Arial, sans-serif; }
		table { width: 100%; border-collapse: collapse; margin-top: 10px; }
		th { border: 1px solid black; padding: 8px; text-align: center; background-color: #f2f2f2 }
		td { border: 1px solid black; padding: 8px; text-align: left; }
		td.right { text-align: right; }
		td.center { text-align: center; }

		.total { text-align: right; font-weight: bold; margin-top: 10px; }
		.title { text-align: center; font-weight: bold; margin-top: 10px; }
		.name { text-align: right; font-weight: bold; }
	</style>
</head>
<body>
	<hr>
	<h1 class="name">LEO AutoParts</h1>
	<hr>
	
	<h3 class="title">Factura #{{ $sale->id }}</h3>
	
	<p><strong>Cliente:</strong> {{ $sale->customer->name }}</p>
	<p><strong>Vendedor:</strong> {{ $sale->user->name }}</p>
	<p><strong>Fecha:</strong> {{ $sale->sale_date }}</p>
	
	<table>
		<thead>
			<tr>
				<th>Producto</th>
				<th>Cantidad</th>
				<th>Precio</th>
				<th>Subtotal</th>
			</tr>
		</thead>
		<tbody>
			@foreach($sale->saleDetails as $detail)
				<tr>
					<td>{{ $detail->product->name }}</td>
					<td class="right">{{ $detail->quantity }}</td>
					<td class="right">${{ \App\Support\Decimal::format($detail->price, 2) }}</td>
					<td class="right">${{ \App\Support\Decimal::format($detail->total, 2) }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
	
	<h3 class="total">Total: ${{ \App\Support\Decimal::format($sale->total, 2) }}</h3>
</body>
</html>