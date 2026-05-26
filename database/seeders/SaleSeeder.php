<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\SaleDetail;

class SaleSeeder extends Seeder
{
	public function run()
	{
		$sales = [
			['customer_id' => 1, 'user_id' => 1, 'total' => 150.00, 'sale_date' => '2024-09-10 14:30:00', 'payment_method_id' => 2],
			['customer_id' => 2, 'user_id' => 1, 'total' => 200.00, 'sale_date' => '2024-09-15 10:45:00', 'payment_method_id' => 2],
			['customer_id' => 3, 'user_id' => 1, 'total' => 180.00, 'sale_date' => '2024-09-20 16:00:00', 'payment_method_id' => 2],
			['customer_id' => 4, 'user_id' => 1, 'total' => 120.00, 'sale_date' => '2024-08-05 11:20:00', 'payment_method_id' => 2],
			['customer_id' => 5, 'user_id' => 2, 'total' => 300.00, 'sale_date' => '2024-08-18 13:10:00', 'payment_method_id' => 1],
			['customer_id' => 6, 'user_id' => 3, 'total' => 250.00, 'sale_date' => '2024-08-25 09:30:00', 'payment_method_id' => 2],
			['customer_id' => 7, 'user_id' => 1, 'total' => 90.00, 'sale_date' => '2024-07-07 14:00:00', 'payment_method_id' => 1],
			['customer_id' => 8, 'user_id' => 2, 'total' => 180.00, 'sale_date' => '2024-07-12 15:30:00', 'payment_method_id' => 2],
			['customer_id' => 9, 'user_id' => 2, 'total' => 160.00, 'sale_date' => '2024-07-22 12:00:00', 'payment_method_id' => 2],
			['customer_id' => 10, 'user_id' => 2, 'total' => 140.00, 'sale_date' => '2024-06-03 17:45:00', 'payment_method_id' => 2],
			['customer_id' => 11, 'user_id' => 1, 'total' => 210.00, 'sale_date' => '2024-06-15 08:15:00', 'payment_method_id' => 2],
			['customer_id' => 12, 'user_id' => 1, 'total' => 190.00, 'sale_date' => '2024-06-28 11:50:00', 'payment_method_id' => 2],
		];
		
		$saleDetails = [
			['sale_id' => 1, 'product_id' => 5, 'quantity' => 2, 'price' => 75.00, 'total' => 150.00],
			['sale_id' => 2, 'product_id' => 12, 'quantity' => 4, 'price' => 50.00, 'total' => 200.00],
			['sale_id' => 3, 'product_id' => 8, 'quantity' => 3, 'price' => 60.00, 'total' => 180.00],
			['sale_id' => 4, 'product_id' => 3, 'quantity' => 1, 'price' => 120.00, 'total' => 120.00],
			['sale_id' => 5, 'product_id' => 9, 'quantity' => 5, 'price' => 60.00, 'total' => 300.00],
			['sale_id' => 6, 'product_id' => 15, 'quantity' => 2, 'price' => 125.00, 'total' => 250.00],
			['sale_id' => 7, 'product_id' => 1, 'quantity' => 3, 'price' => 30.00, 'total' => 90.00],
			['sale_id' => 8, 'product_id' => 18, 'quantity' => 6, 'price' => 30.00, 'total' => 180.00],
			['sale_id' => 9, 'product_id' => 22, 'quantity' => 4, 'price' => 40.00, 'total' => 160.00],
			['sale_id' => 10, 'product_id' => 27, 'quantity' => 2, 'price' => 70.00, 'total' => 140.00],
			['sale_id' => 11, 'product_id' => 30, 'quantity' => 3, 'price' => 70.00, 'total' => 210.00],
			['sale_id' => 12, 'product_id' => 10, 'quantity' => 4, 'price' => 47.50, 'total' => 190.00],
		];

		foreach ($sales as $sale) { Sale::create($sale); }
		foreach ($saleDetails as $detail) { SaleDetail::create($detail); }
		
		// // Crear al menos 10 ventas
		// for ($i = 1; $i <= 10; $i++) {
		//     // Generar una fecha aleatoria entre los últimos 2 meses
		//     $saleDate = Carbon::now()->subDays(rand(1, 180));  // Fecha entre hoy y hace 60 días

		//     // Crear una venta con un cliente y un vendedor aleatorio
		//     $sale = Sale::create([
		//         'customer_id' => Customer::all()->random()->id,  // Cliente aleatorio
		//         'user_id' => User::inRandomOrder()->first()->id,  // Vendedor aleatorio
		//         'total' => rand(100, 1000),  // Total aleatorio de la venta
		//         'sale_date' => $saleDate,  // Asignar la fecha aleatoria generada
		//         'payment_method_id' => PaymentMethod::all()->random()->id,  // Método de pago aleatorio
		//     ]);

		//     // Crear entre 1 y 5 detalles de venta para cada venta
		//     $products = Product::inRandomOrder()->take(rand(1, 5))->get();

		//     foreach ($products as $product) {
		//         // Calcular el total del producto en la venta (precio * cantidad)
		//         $quantity = rand(1, 3);  // Cantidad aleatoria de productos
		//         $total = $product->price * $quantity;

		//         // Crear el detalle de venta
		//         SaleDetail::create([
		//             'sale_id' => $sale->id,  // Relacionamos el detalle con la venta
		//             'product_id' => $product->id,  // Producto asociado
		//             'quantity' => $quantity,  // Cantidad vendida
		//             'price' => $product->price,  // Precio unitario
		//             'total' => $total,  // Total por este producto en esta venta (cantidad * precio)
		//         ]);
		//     }
		// }
	}
}
