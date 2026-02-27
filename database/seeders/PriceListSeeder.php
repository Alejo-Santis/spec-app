<?php

namespace Database\Seeders;

use App\Models\BundleTier;
use App\Models\PriceList;
use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class PriceListSeeder extends Seeder
{
    public function run(): void
    {
        $list = PriceList::firstOrCreate(
            ['year' => 2026],
            [
                'name'                  => 'Lista de Precios 2026',
                'adjustment_percentage' => 9.00,
                'is_active'             => true,
            ]
        );

        $bundleCert = ServiceType::where('name', 'Bolsa Certificados')->first();

        if ($bundleCert) {
            $tiers = [
                ['name' => 'Bolsa 500',      'quantity' => 500,   'price' => 196630],
                ['name' => 'Bolsa 1.000',    'quantity' => 1000,  'price' => 317735],
                ['name' => 'Bolsa 3.000',    'quantity' => 3000,  'price' => 375105],
                ['name' => 'Bolsa 5.000',    'quantity' => 5000,  'price' => 433275],
                ['name' => 'Bolsa 7.000',    'quantity' => 7000,  'price' => 479711],
                ['name' => 'Paquete 10.000', 'quantity' => 10000, 'price' => 623916],
                ['name' => 'Paquete 15.000', 'quantity' => 15000, 'price' => 779895],
            ];

            foreach ($tiers as $tier) {
                BundleTier::firstOrCreate(
                    [
                        'service_type_id' => $bundleCert->id,
                        'price_list_id'   => $list->id,
                        'name'            => $tier['name'],
                    ],
                    [
                        'quantity' => $tier['quantity'],
                        'price'    => $tier['price'],
                    ]
                );
            }
        }
    }
}
