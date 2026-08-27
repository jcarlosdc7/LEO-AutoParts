<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliersData = [
            ['name' => 'Repuestos AutoPlus', 'contact' => 'www.repuestosautoplus@support.com', 'phone' => '12345678', 'address' => 'Calle Falsa 101'],
            ['name' => 'Repuestos MotorPro', 'contact' => 'www.repuestosmotorpro@support.com', 'phone' => '87654321', 'address' => 'Calle Falsa 203'],
            ['name' => 'Distribuidora AutoRep', 'contact' => 'www.distribuidoraautorep@support.com', 'phone' => '23456789', 'address' => 'Calle Falsa 150'],
            ['name' => 'Repuestos Universales', 'contact' => 'www.repuestosuniversales@support.com', 'phone' => '98765432', 'address' => 'Calle Falsa 178'],
            ['name' => 'RepAuto Express', 'contact' => 'www.repautoexpress@support.com', 'phone' => '34567890', 'address' => 'Calle Falsa 116'],
            ['name' => 'Accesorios y Repuestos', 'contact' => 'www.accesoriosyrepuestos@support.com', 'phone' => '09876543', 'address' => 'Calle Falsa 225'],
            ['name' => 'Distribuciones Automotrices', 'contact' => 'www.distribucionesautomotrices@support.com', 'phone' => '45678901', 'address' => 'Calle Falsa 300'],
            ['name' => 'Repuestos Total', 'contact' => 'www.repuestostotal@support.com', 'phone' => '12349876', 'address' => 'Calle Falsa 275'],
            ['name' => 'RepXpress', 'contact' => 'www.repxpress@support.com', 'phone' => '67890123', 'address' => 'Calle Falsa 189'],
            ['name' => 'RepAuto Solutions', 'contact' => 'www.repautosolutions@support.com', 'phone' => '87651234', 'address' => 'Calle Falsa 145'],
            ['name' => 'Autopartes y Más', 'contact' => 'www.autopartesymas@support.com', 'phone' => '23458976', 'address' => 'Calle Falsa 210'],
            ['name' => 'Repuestos Globales', 'contact' => 'www.repuestosglobales@support.com', 'phone' => '98764321', 'address' => 'Calle Falsa 134'],
            ['name' => 'Repuestos Automotrices', 'contact' => 'www.repuestosautomotrices@support.com', 'phone' => '34561278', 'address' => 'Calle Falsa 109'],
            ['name' => 'Proveedora de Repuestos', 'contact' => 'www.proveedoraderepuestos@support.com', 'phone' => '65432189', 'address' => 'Calle Falsa 241'],
            ['name' => 'Repuestos y Accesorios', 'contact' => 'www.repuestosyaccesorios@support.com', 'phone' => '78901234', 'address' => 'Calle Falsa 162'],
            ['name' => 'Distribuidor Repuestos 360', 'contact' => 'www.repuestos360@support.com', 'phone' => '87654321', 'address' => 'Calle Falsa 199'],
            ['name' => 'AutoRep Parts', 'contact' => 'www.autorepparts@support.com', 'phone' => '23456789', 'address' => 'Calle Falsa 180'],
            ['name' => 'Repuestos del Futuro', 'contact' => 'www.repuestosdelfuturo@support.com', 'phone' => '98765432', 'address' => 'Calle Falsa 277'],
            ['name' => 'Distribuciones Repucom', 'contact' => 'www.repucom@support.com', 'phone' => '34567890', 'address' => 'Calle Falsa 142'],
            ['name' => 'Autopartes Express', 'contact' => 'www.autopartesexpress@support.com', 'phone' => '09876543', 'address' => 'Calle Falsa 255'],
        ];

        foreach ($suppliersData as $supplierData) {
            $supplier = new Supplier; // Crea una nueva instancia del modelo
            $supplier->fill($supplierData); // Llena el modelo con los datos del array
            $supplier->save(); // Guarda el modelo en la base de datos
        }
    }
}
