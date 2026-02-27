<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Certificado 1 año',  'billing_type' => 'unit',   'applies_iva' => true,  'iva_percentage' => 19.00],
            ['name' => 'Certificado 2 años', 'billing_type' => 'unit',   'applies_iva' => true,  'iva_percentage' => 19.00],
            ['name' => 'Habilitación',       'billing_type' => 'unit',   'applies_iva' => false, 'iva_percentage' => 0.00],
            ['name' => 'Documento',          'billing_type' => 'unit',   'applies_iva' => false, 'iva_percentage' => 0.00],
            ['name' => 'Bolsa Certificados', 'billing_type' => 'bundle', 'applies_iva' => true,  'iva_percentage' => 19.00],
            ['name' => 'Bolsa Documentos',   'billing_type' => 'bundle', 'applies_iva' => false, 'iva_percentage' => 0.00],
        ];

        foreach ($types as $type) {
            ServiceType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
