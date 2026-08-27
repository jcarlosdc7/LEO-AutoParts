<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoriesData = [
            ['name' => 'Filtros'],
            ['name' => 'Frenos'],
            ['name' => 'Bujías y Encendido'],
            ['name' => 'Amortiguadores y Suspensión'],
            ['name' => 'Correas y Cadenas de Distribución'],
            ['name' => 'Aceites y Lubricantes'],
            ['name' => 'Baterías'],
            ['name' => 'Accesorios y Herramientas'],
        ];

        foreach ($categoriesData as $categoryData) {
            $category = new Category; // Crea una nueva instancia del modelo
            $category->fill($categoryData); // Llena el modelo con los datos del array
            $category->save(); // Guarda el modelo en la base de datos
        }
    }
}
