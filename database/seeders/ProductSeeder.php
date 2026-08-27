<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productsData = [
            ['code' => '001', 'name' => 'Filtro de Aceite', 'description' => 'Filtro de aceite para motor de vehículos con motor de 2.0L', 'brand' => 'Bosch', 'model' => 'F-1000', 'supplier_id' => 1, 'category_id' => 1, 'stock' => 150, 'min_stock' => 20, 'price' => 25.99, 'image_path' => 'productImages/product_001_image.jpg'],
            ['code' => '002', 'name' => 'Filtro de Aire', 'description' => 'Filtro de aire para vehículos con motor de 1.8L a 2.5L', 'brand' => 'Mann-Filter', 'model' => 'C-4000', 'supplier_id' => 2, 'category_id' => 1, 'stock' => 300, 'min_stock' => 40, 'price' => 15.99, 'image_path' => 'productImages/product_002_image.jpg'],
            ['code' => '003', 'name' => 'Pastillas de Freno', 'description' => 'Pastillas de freno para vehículos sedán y SUV', 'brand' => 'Delphi', 'model' => 'D-2000', 'supplier_id' => 3, 'category_id' => 2, 'stock' => 200, 'min_stock' => 30, 'price' => 49.99, 'image_path' => 'productImages/product_003_image.jpg'],
            ['code' => '004', 'name' => 'Disco de Freno', 'description' => 'Disco de freno para vehículos compactos y SUV', 'brand' => 'Brembo', 'model' => 'B-3000', 'supplier_id' => 4, 'category_id' => 2, 'stock' => 180, 'min_stock' => 25, 'price' => 75.99, 'image_path' => 'productImages/product_004_image.jpg'],
            ['code' => '005', 'name' => 'Bujías', 'description' => 'Bujías para motores de 4 cilindros, compatible con varias marcas', 'brand' => 'NGK', 'model' => 'B7ECS', 'supplier_id' => 5, 'category_id' => 3, 'stock' => 500, 'min_stock' => 50, 'price' => 12.99, 'image_path' => 'productImages/product_005_image.jpg'],
            ['code' => '006', 'name' => 'Cables de Encendido', 'description' => 'Cables de encendido para vehículos de motor 1.6L a 2.0L', 'brand' => 'Bosch', 'model' => 'CE-2000', 'supplier_id' => 6, 'category_id' => 3, 'stock' => 250, 'min_stock' => 30, 'price' => 35.99, 'image_path' => 'productImages/product_006_image.jpg'],
            ['code' => '007', 'name' => 'Amortiguadores Traseros', 'description' => 'Amortiguadores traseros para vehículos compactos', 'brand' => 'Monroe', 'model' => 'M-850', 'supplier_id' => 7, 'category_id' => 4, 'stock' => 80, 'min_stock' => 15, 'price' => 79.99, 'image_path' => 'productImages/product_007_image.jpg'],
            ['code' => '008', 'name' => 'Amortiguadores Delanteros', 'description' => 'Amortiguadores delanteros para SUV y camionetas', 'brand' => 'KYB', 'model' => 'KYB-3000', 'supplier_id' => 8, 'category_id' => 4, 'stock' => 60, 'min_stock' => 10, 'price' => 95.99, 'image_path' => 'productImages/product_008_image.jpg'],
            ['code' => '009', 'name' => 'Correa de Distribución', 'description' => 'Correa de distribución para vehículos con motor 1.6L', 'brand' => 'Dayco', 'model' => 'D-1064', 'supplier_id' => 9, 'category_id' => 5, 'stock' => 60, 'min_stock' => 10, 'price' => 45.99, 'image_path' => 'productImages/product_009_image.jpg'],
            ['code' => '010', 'name' => 'Aceite de Motor 5W30', 'description' => 'Aceite de motor sintético para vehículos con motores de alto rendimiento', 'brand' => 'Castrol', 'model' => 'Edge 5W30', 'supplier_id' => 10, 'category_id' => 6, 'stock' => 120, 'min_stock' => 20, 'price' => 35.99, 'image_path' => 'productImages/product_010_image.jpg'],
            ['code' => '011', 'name' => 'Batería de Automóvil', 'description' => 'Batería AGM para vehículos de alto rendimiento', 'brand' => 'Varta', 'model' => 'AGM-6800', 'supplier_id' => 11, 'category_id' => 7, 'stock' => 50, 'min_stock' => 5, 'price' => 129.99, 'image_path' => 'productImages/product_011_image.jpg'],
            ['code' => '012', 'name' => 'Cables de Arranque', 'description' => 'Cables de arranque de 4.5 metros para vehículos de motor de combustión', 'brand' => 'Stanley', 'model' => 'J-9000', 'supplier_id' => 12, 'category_id' => 8, 'stock' => 120, 'min_stock' => 15, 'price' => 19.99, 'image_path' => 'productImages/product_012_image.jpg'],
            ['code' => '013', 'name' => 'Filtro de Aceite para Camión', 'description' => 'Filtro de aceite para camiones de carga pesada', 'brand' => 'WIX', 'model' => 'F-1500', 'supplier_id' => 13, 'category_id' => 1, 'stock' => 100, 'min_stock' => 10, 'price' => 45.99, 'image_path' => 'productImages/product_013_image.jpg'],
            ['code' => '014', 'name' => 'Frenos de Disco', 'description' => 'Frenos de disco para vehículos con sistema ABS', 'brand' => 'Bosch', 'model' => 'D-1800', 'supplier_id' => 14, 'category_id' => 2, 'stock' => 250, 'min_stock' => 50, 'price' => 65.99, 'image_path' => 'productImages/product_014_image.jpg'],
            ['code' => '015', 'name' => 'Filtro de Aire para SUV', 'description' => 'Filtro de aire para SUV con motor 2.0L a 3.5L', 'brand' => 'K&N', 'model' => 'K-1100', 'supplier_id' => 15, 'category_id' => 1, 'stock' => 150, 'min_stock' => 20, 'price' => 35.99, 'image_path' => 'productImages/product_015_image.jpg'],
            ['code' => '016', 'name' => 'Aceite para Transmisión', 'description' => 'Aceite para transmisión automática de vehículos', 'brand' => 'Valvoline', 'model' => 'ATF-3', 'supplier_id' => 16, 'category_id' => 6, 'stock' => 200, 'min_stock' => 25, 'price' => 50.99, 'image_path' => 'productImages/product_016_image.jpg'],
            ['code' => '017', 'name' => 'Bujías de Encendido', 'description' => 'Bujías de encendido para vehículos de motores de 3 cilindros', 'brand' => 'Denso', 'model' => 'K20PR-U11', 'supplier_id' => 17, 'category_id' => 3, 'stock' => 400, 'min_stock' => 40, 'price' => 18.99, 'image_path' => 'productImages/product_017_image.jpg'],
            ['code' => '018', 'name' => 'Cables de Encendido para SUV', 'description' => 'Cables de encendido para vehículos SUV de motor 3.5L', 'brand' => 'NGK', 'model' => 'CE-2500', 'supplier_id' => 18, 'category_id' => 3, 'stock' => 150, 'min_stock' => 20, 'price' => 40.99, 'image_path' => 'productImages/product_018_image.jpg'],
            ['code' => '019', 'name' => 'Filtro de Combustible', 'description' => 'Filtro de combustible para vehículos con motor diésel', 'brand' => 'Mann-Filter', 'model' => 'C-1200', 'supplier_id' => 19, 'category_id' => 1, 'stock' => 180, 'min_stock' => 20, 'price' => 22.99, 'image_path' => 'productImages/product_019_image.jpg'],
            ['code' => '020', 'name' => 'Aceite Sintético 5W20', 'description' => 'Aceite sintético de alto rendimiento para vehículos deportivos', 'brand' => 'Royal Purple', 'model' => '5W20', 'supplier_id' => 20, 'category_id' => 6, 'stock' => 100, 'min_stock' => 15, 'price' => 45.99, 'image_path' => 'productImages/product_020_image.jpg'],
            ['code' => '021', 'name' => 'Cables para Bujías', 'description' => 'Cables de alta calidad para bujías en vehículos de alto rendimiento', 'brand' => 'MSD', 'model' => '8.5MM', 'supplier_id' => 1, 'category_id' => 3, 'stock' => 80, 'min_stock' => 10, 'price' => 49.99, 'image_path' => 'productImages/product_021_image.jpg'],
            ['code' => '022', 'name' => 'Amortiguadores de Suspensión', 'description' => 'Amortiguadores de suspensión para vehículos con tracción delantera', 'brand' => 'Bilstein', 'model' => 'B-4000', 'supplier_id' => 2, 'category_id' => 4, 'stock' => 150, 'min_stock' => 20, 'price' => 99.99, 'image_path' => 'productImages/product_022_image.jpg'],
            ['code' => '023', 'name' => 'Kit de Pastillas de Freno', 'description' => 'Kit completo de pastillas de freno para vehículos deportivos', 'brand' => 'Ferodo', 'model' => 'F-2200', 'supplier_id' => 3, 'category_id' => 2, 'stock' => 200, 'min_stock' => 30, 'price' => 85.99, 'image_path' => 'productImages/product_023_image.jpg'],
            ['code' => '024', 'name' => 'Freno de Mano', 'description' => 'Freno de mano hidráulico para vehículos de alto rendimiento', 'brand' => 'Wilwood', 'model' => 'FH-5000', 'supplier_id' => 4, 'category_id' => 2, 'stock' => 50, 'min_stock' => 5, 'price' => 150.99, 'image_path' => 'productImages/product_024_image.jpg'],
            ['code' => '025', 'name' => 'Aceite de Motor 10W40', 'description' => 'Aceite de motor para vehículos con motor de alto kilometraje', 'brand' => 'Mobil 1', 'model' => '10W40', 'supplier_id' => 5, 'category_id' => 6, 'stock' => 250, 'min_stock' => 30, 'price' => 32.99, 'image_path' => 'productImages/product_025_image.jpg'],
            ['code' => '026', 'name' => 'Filtro de Aire para Camión', 'description' => 'Filtro de aire de alto rendimiento para camiones de carga', 'brand' => 'Donaldson', 'model' => 'F-3500', 'supplier_id' => 6, 'category_id' => 1, 'stock' => 100, 'min_stock' => 10, 'price' => 55.99, 'image_path' => 'productImages/product_026_image.jpg'],
            ['code' => '027', 'name' => 'Bujías de Alto Rendimiento', 'description' => 'Bujías para motores de alto rendimiento', 'brand' => 'ACDelco', 'model' => 'R-5000', 'supplier_id' => 7, 'category_id' => 3, 'stock' => 400, 'min_stock' => 40, 'price' => 25.99, 'image_path' => 'productImages/product_027_image.jpg'],
            ['code' => '028', 'name' => 'Amortiguadores para Pick-Up', 'description' => 'Amortiguadores para vehículos de tipo Pick-Up', 'brand' => 'Rancho', 'model' => 'RS-5000', 'supplier_id' => 8, 'category_id' => 4, 'stock' => 70, 'min_stock' => 10, 'price' => 125.99, 'image_path' => 'productImages/product_028_image.jpg'],
            ['code' => '029', 'name' => 'Correa de Distribución para SUV', 'description' => 'Correa de distribución para SUV de 2.5L a 3.0L', 'brand' => 'Gates', 'model' => 'G-5000', 'supplier_id' => 9, 'category_id' => 5, 'stock' => 150, 'min_stock' => 20, 'price' => 60.99, 'image_path' => 'productImages/product_029_image.jpg'],
            ['code' => '030', 'name' => 'Batería para Vehículos Eléctricos', 'description' => 'Batería de litio para vehículos eléctricos', 'brand' => 'Panasonic', 'model' => 'EV-3000', 'supplier_id' => 10, 'category_id' => 7, 'stock' => 30, 'min_stock' => 3, 'price' => 500.99, 'image_path' => 'productImages/product_030_image.jpg'],
        ];

        foreach ($productsData as $productData) {
            $product = new Product; // Crea una nueva instancia del modelo
            $product->fill($productData); // Llena el modelo con los datos del array
            $product->save(); // Guarda el modelo en la base de datos
        }
    }
}
