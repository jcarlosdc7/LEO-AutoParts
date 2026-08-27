<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customersData = [
            ['dni_ruc' => '281-000000-0000X', 'name' => 'Carlos Alberto Pérez', 'email' => 'caperez777@email.com', 'phone' => '88810001', 'address' => 'Bo. San Juan', 'city' => 'León', 'customer_type_id' => 3],
            ['dni_ruc' => '281-000000-0001X', 'name' => 'María Fernanda Gómez', 'email' => 'mfgomez777@email.com', 'phone' => '88810002', 'address' => 'Bo. La Repartición', 'city' => 'Managua', 'customer_type_id' => 2],
            ['dni_ruc' => '281-000000-0002X', 'name' => 'Juan Pablo Martínez', 'email' => 'jpmartinez777@email.com', 'phone' => '88810003', 'address' => 'Bo. El Calvario', 'city' => 'Chinandega', 'customer_type_id' => 1],
            ['dni_ruc' => '281-000000-0003X', 'name' => 'Ana Sofía Rodríguez', 'email' => 'asrodriguez777@email.com', 'phone' => '88810004', 'address' => 'Bo. El Edén', 'city' => 'Estelí', 'customer_type_id' => 2],
            ['dni_ruc' => '281-000000-0004X', 'name' => 'Luis Enrique López', 'email' => 'llopez777@email.com', 'phone' => '88810005', 'address' => 'Bo. La Merced', 'city' => 'Matagalpa', 'customer_type_id' => 3],
            ['dni_ruc' => '281-000000-0005X', 'name' => 'Elena Patricia García', 'email' => 'epgarcia777@email.com', 'phone' => '88810006', 'address' => 'Bo. Los Robles', 'city' => 'Granada', 'customer_type_id' => 1],
            ['dni_ruc' => '281-000000-0006X', 'name' => 'Felipe Andrés Sánchez', 'email' => 'fasanchez777@email.com', 'phone' => '88810007', 'address' => 'Bo. El Calvario', 'city' => 'Rivas', 'customer_type_id' => 3],
            ['dni_ruc' => '281-000000-0007X', 'name' => 'Verónica Ivette Fernández', 'email' => 'vifernandez777@email.com', 'phone' => '88810008', 'address' => 'Bo. La Libertad', 'city' => 'Bluefields', 'customer_type_id' => 2],
            ['dni_ruc' => '281-000000-0008X', 'name' => 'Ricardo Javier Ramírez', 'email' => 'rjramirez777@email.com', 'phone' => '88810009', 'address' => 'Bo. La Gloria', 'city' => 'Matagalpa', 'customer_type_id' => 3],
            ['dni_ruc' => '281-000000-0009X', 'name' => 'Marta Isabel Díaz', 'email' => 'midiaz777@email.com', 'phone' => '88810010', 'address' => 'Bo. Santa Fe', 'city' => 'Masaya', 'customer_type_id' => 1],
            ['dni_ruc' => '281-000000-0010X', 'name' => 'Diego Armando Mendoza', 'email' => 'damendoza777@email.com', 'phone' => '88810011', 'address' => 'Bo. San Isidro', 'city' => 'León', 'customer_type_id' => 1],
            ['dni_ruc' => '281-000000-0011X', 'name' => 'Liliana Teresa Pineda', 'email' => 'ltpineda777@email.com', 'phone' => '88810012', 'address' => 'Bo. El Dorado', 'city' => 'Chinandega', 'customer_type_id' => 2],
            ['dni_ruc' => '281-000000-0012X', 'name' => 'Juan José Ortega', 'email' => 'jjo777@email.com', 'phone' => '88810013', 'address' => 'Bo. Las Piedras', 'city' => 'Estelí', 'customer_type_id' => 3],
            ['dni_ruc' => '281-000000-0013X', 'name' => 'Marina Isabel Silva', 'email' => 'misilva777@email.com', 'phone' => '88810014', 'address' => 'Bo. Santa María', 'city' => 'Managua', 'customer_type_id' => 2],
            ['dni_ruc' => '281-000000-0014X', 'name' => 'Carlos José Romero', 'email' => 'cjoromo777@email.com', 'phone' => '88810015', 'address' => 'Bo. San Martín', 'city' => 'Granada', 'customer_type_id' => 3],
            ['dni_ruc' => '281-000000-0015X', 'name' => 'Paola Lorena Vega', 'email' => 'plvega777@email.com', 'phone' => '88810016', 'address' => 'Bo. El Naranjal', 'city' => 'Bluefields', 'customer_type_id' => 1],
            ['dni_ruc' => '281-000000-0016X', 'name' => 'Víctor Manuel Gutiérrez', 'email' => 'vmgutiérrez777@email.com', 'phone' => '88810017', 'address' => 'Bo. La Esperanza', 'city' => 'Rivas', 'customer_type_id' => 3],
            ['dni_ruc' => '281-000000-0017X', 'name' => 'Natalia Beatriz Sánchez', 'email' => 'nbsanchez777@email.com', 'phone' => '88810018', 'address' => 'Bo. El Prado', 'city' => 'Masaya', 'customer_type_id' => 2],
        ];

        foreach ($customersData as $customerData) {
            $category = new Customer; // Crea una nueva instancia del modelo
            $category->fill($customerData); // Llena el modelo con los datos del array
            $category->save(); // Guarda el modelo en la base de datos
        }
    }
}
