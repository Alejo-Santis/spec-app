<?php

namespace Database\Seeders;

use App\Models\BundleConsumption;
use App\Models\BundleTier;
use App\Models\Client;
use App\Models\ClientBundle;
use App\Models\ClientPrice;
use App\Models\MonthlyUsage;
use App\Models\PriceAdjustment;
use App\Models\PriceList;
use App\Models\ServiceType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * DemoDataSeeder
 *
 * Crea datos de demostración para todas las funcionalidades del sistema.
 * Ejecutar: php artisan db:seed --class=DemoDataSeeder
 *
 * NOTA: Este seeder es idempotente — si los datos ya existen (por document_number),
 * los omite. Limpiar antes con: php artisan migrate:fresh --seed
 */
class DemoDataSeeder extends Seeder
{
    private User $adminUser;
    private PriceList $list2025;
    private PriceList $list2026;

    public function run(): void
    {
        $this->adminUser = User::where('email', 'admin@spec.co')->firstOrFail();

        $this->seedServiceTypes();
        $this->seedPriceLists();
        $this->seedClients();
        $this->seedClientPrices();
        $this->seedClientBundles();
        $this->seedMonthlyUsages();

        $this->command->info('✓ DemoDataSeeder completado.');
    }

    // ────────────────────────────────────────────────────────
    // 1. Tipos de servicio — añadir "Facturación Electrónica Ilimitada"
    // ────────────────────────────────────────────────────────
    private function seedServiceTypes(): void
    {
        ServiceType::firstOrCreate(
            ['name' => 'Facturación Electrónica Ilimitada'],
            [
                'billing_type'   => 'metered',
                'applies_iva'    => false,
                'iva_percentage' => 0,
                'description'    => 'Servicio de facturación electrónica con tarifa mensual por documento emitido. Sin límite de volumen.',
                'is_active'      => true,
            ]
        );

        ServiceType::firstOrCreate(
            ['name' => 'Bolsa Habilitaciones'],
            [
                'billing_type'   => 'bundle',
                'applies_iva'    => false,
                'iva_percentage' => 0,
                'description'    => 'Paquete prepagado de habilitaciones.',
                'is_active'      => true,
            ]
        );
    }

    // ────────────────────────────────────────────────────────
    // 2. Listas de precios — crear 2025 (inactiva) y asegurar 2026
    // ────────────────────────────────────────────────────────
    private function seedPriceLists(): void
    {
        $this->list2025 = PriceList::firstOrCreate(
            ['year' => 2025],
            [
                'name'                  => 'Lista de Precios 2025',
                'adjustment_percentage' => 12.00,
                'is_active'             => false,
                'notes'                 => 'Lista del año anterior. Usada como base para generar la 2026.',
            ]
        );

        $this->list2026 = PriceList::where('year', 2026)->firstOrFail();

        // Bundle tiers 2025
        $bundleCert2025 = ServiceType::where('name', 'Bolsa Certificados')->first();
        if ($bundleCert2025) {
            $tiers2025 = [
                ['name' => 'Bolsa 500',      'quantity' => 500,   'price' => 180395],
                ['name' => 'Bolsa 1.000',    'quantity' => 1000,  'price' => 291500],
                ['name' => 'Bolsa 3.000',    'quantity' => 3000,  'price' => 344135],
                ['name' => 'Bolsa 5.000',    'quantity' => 5000,  'price' => 397500],
                ['name' => 'Paquete 10.000', 'quantity' => 10000, 'price' => 572400],
            ];
            foreach ($tiers2025 as $t) {
                BundleTier::firstOrCreate(
                    ['service_type_id' => $bundleCert2025->id, 'price_list_id' => $this->list2025->id, 'name' => $t['name']],
                    ['quantity' => $t['quantity'], 'price' => $t['price']]
                );
            }
        }

        // Bundle tiers 2026 — Bolsa Habilitaciones
        $bundleHab = ServiceType::where('name', 'Bolsa Habilitaciones')->first();
        if ($bundleHab) {
            $tiersHab = [
                ['name' => 'Paquete 50 Hab.',  'quantity' => 50,  'price' => 1250000],
                ['name' => 'Paquete 100 Hab.', 'quantity' => 100, 'price' => 2200000],
                ['name' => 'Paquete 200 Hab.', 'quantity' => 200, 'price' => 3900000],
            ];
            foreach ($tiersHab as $t) {
                BundleTier::firstOrCreate(
                    ['service_type_id' => $bundleHab->id, 'price_list_id' => $this->list2026->id, 'name' => $t['name']],
                    ['quantity' => $t['quantity'], 'price' => $t['price']]
                );
            }
        }
    }

    // ────────────────────────────────────────────────────────
    // 3. Clientes demo
    // ────────────────────────────────────────────────────────
    private function seedClients(): void
    {
        $clients = [
            [
                'type'                 => 'juridica',
                'business_name'        => 'EMPRESA DEMO S.A.S.',
                'trade_name'           => 'Demo Corp',
                'document_number'      => '900123456',
                'dv'                   => '7',
                'tax_regime'           => 'ordinario',
                'tax_responsibilities' => ['O-13', 'O-15'],
                'ciiu_code'            => '6201',
                'email'                => 'contacto@empresademo.co',
                'email_billing'        => 'facturacion@empresademo.co',
                'phone'                => '6014567890',
                'mobile'               => '3001234567',
                'address'              => 'Cra 15 # 93-47, Of 502',
                'city'                 => 'Bogotá',
                'department'           => 'Cundinamarca',
                'is_active'            => true,
            ],
            [
                'type'                 => 'juridica',
                'business_name'        => 'TECNOLOGÍA AVANZADA LTDA.',
                'trade_name'           => 'TechAv',
                'document_number'      => '800987654',
                'dv'                   => '3',
                'tax_regime'           => 'simple',
                'tax_responsibilities' => ['O-47'],
                'ciiu_code'            => '6311',
                'email'                => 'admin@techav.co',
                'email_billing'        => 'cuentas@techav.co',
                'phone'                => '6017891234',
                'mobile'               => '3157894561',
                'address'              => 'Calle 72 # 12-31, Piso 8',
                'city'                 => 'Bogotá',
                'department'           => 'Cundinamarca',
                'is_active'            => true,
            ],
            [
                'type'                 => 'natural',
                'business_name'        => 'Carlos Ramírez Morales',
                'document_number'      => '1023456789',
                'tax_regime'           => 'ordinario',
                'tax_responsibilities' => ['R-99-PN'],
                'email'                => 'carlos.ramirez@gmail.com',
                'mobile'               => '3209876543',
                'city'                 => 'Medellín',
                'department'           => 'Antioquia',
                'is_active'            => true,
            ],
            [
                'type'                 => 'juridica',
                'business_name'        => 'INVERSIONES DEL VALLE S.A.',
                'document_number'      => '890654321',
                'dv'                   => '1',
                'tax_regime'           => 'ordinario',
                'tax_responsibilities' => ['O-13'],
                'ciiu_code'            => '6420',
                'email'                => 'gerencia@invvalle.co',
                'email_billing'        => 'contabilidad@invvalle.co',
                'phone'                => '6024567890',
                'city'                 => 'Cali',
                'department'           => 'Valle del Cauca',
                'is_active'            => true,
            ],
            [
                'type'                 => 'juridica',
                'business_name'        => 'SERVICIOS LOGÍSTICOS ANDINOS S.A.S.',
                'trade_name'           => 'LogiAndina',
                'document_number'      => '901234567',
                'dv'                   => '5',
                'tax_regime'           => 'ordinario',
                'tax_responsibilities' => ['O-15', 'O-23'],
                'ciiu_code'            => '5210',
                'email'                => 'info@logiandina.co',
                'email_billing'        => 'facturacion@logiandina.co',
                'phone'                => '6074561234',
                'mobile'               => '3124567890',
                'city'                 => 'Barranquilla',
                'department'           => 'Atlántico',
                'is_active'            => true,
            ],
            [
                'type'                 => 'natural',
                'business_name'        => 'María González Hernández',
                'document_number'      => '52987654',
                'tax_regime'           => 'simple',
                'tax_responsibilities' => ['R-99-PN'],
                'email'                => 'maria.gonzalez@outlook.com',
                'mobile'               => '3005671234',
                'city'                 => 'Bogotá',
                'department'           => 'Cundinamarca',
                'is_active'            => true,
            ],
            [
                'type'                 => 'juridica',
                'business_name'        => 'CONSTRUCTORA ANDINA S.A.S.',
                'document_number'      => '830456789',
                'dv'                   => '9',
                'tax_regime'           => 'ordinario',
                'tax_responsibilities' => ['O-13', 'O-23'],
                'ciiu_code'            => '4111',
                'email'                => 'constructora@andina.co',
                'email_billing'        => 'pagos@andina.co',
                'phone'                => '6013456789',
                'mobile'               => '3178901234',
                'city'                 => 'Bogotá',
                'department'           => 'Cundinamarca',
                'is_active'            => true,
            ],
            [
                'type'                 => 'juridica',
                'business_name'        => 'FARMACÉUTICA DEL NORTE LTDA.',
                'document_number'      => '860789012',
                'dv'                   => '2',
                'tax_regime'           => 'ordinario',
                'tax_responsibilities' => ['O-13'],
                'ciiu_code'            => '2100',
                'email'                => 'gerencia@farmanorte.co',
                'email_billing'        => 'facturas@farmanorte.co',
                'phone'                => '6053456789',
                'city'                 => 'Bucaramanga',
                'department'           => 'Santander',
                'is_active'            => true,
            ],
            [
                'type'                 => 'natural',
                'business_name'        => 'Pedro Estrada Jiménez',
                'document_number'      => '79456123',
                'tax_regime'           => 'ordinario',
                'tax_responsibilities' => ['R-99-PN'],
                'email'                => 'p.estrada@empresa.co',
                'mobile'               => '3143210987',
                'city'                 => 'Pereira',
                'department'           => 'Risaralda',
                'is_active'            => true,
            ],
            [
                'type'                 => 'juridica',
                'business_name'        => 'COMERCIALIZADORA PACÍFICO S.A.S.',
                'trade_name'           => 'ComPacífico',
                'document_number'      => '900567891',
                'dv'                   => '4',
                'tax_regime'           => 'ordinario',
                'tax_responsibilities' => ['O-15'],
                'ciiu_code'            => '4690',
                'email'                => 'info@compacifico.co',
                'email_billing'        => 'cxp@compacifico.co',
                'phone'                => '6024560123',
                'mobile'               => '3166543210',
                'city'                 => 'Cali',
                'department'           => 'Valle del Cauca',
                'is_active'            => true,
            ],
            [
                'type'                 => 'juridica',
                'business_name'        => 'DISTRIBUIDORA BOGOTANA S.A.',
                'document_number'      => '860123456',
                'dv'                   => '6',
                'tax_regime'           => 'ordinario',
                'tax_responsibilities' => ['O-13', 'O-15', 'O-23'],
                'ciiu_code'            => '4711',
                'email'                => 'gerencia@distbogotana.co',
                'phone'                => '6013210000',
                'city'                 => 'Bogotá',
                'department'           => 'Cundinamarca',
                'is_active'            => false,  // cliente inactivo para demo
            ],
        ];

        foreach ($clients as $data) {
            Client::firstOrCreate(['document_number' => $data['document_number']], $data);
        }
    }

    // ────────────────────────────────────────────────────────
    // 4. Precios de cliente
    // ────────────────────────────────────────────────────────
    private function seedClientPrices(): void
    {
        $cert1  = ServiceType::where('name', 'Certificado 1 año')->first();
        $cert2  = ServiceType::where('name', 'Certificado 2 años')->first();
        $hab    = ServiceType::where('name', 'Habilitación')->first();
        $doc    = ServiceType::where('name', 'Documento')->first();
        $metered = ServiceType::where('name', 'Facturación Electrónica Ilimitada')->first();

        $l25 = $this->list2025;
        $l26 = $this->list2026;

        $clientMap = Client::whereIn('document_number', [
            '900123456', '800987654', '1023456789', '890654321', '901234567',
            '52987654', '830456789', '860789012', '79456123', '900567891',
        ])->pluck('id', 'document_number');

        $adminId = $this->adminUser->id;

        // Helper closure — skips if already exists
        $createPrice = function (array $data) use ($adminId): ?ClientPrice {
            $exists = ClientPrice::where('client_id', $data['client_id'])
                ->where('service_type_id', $data['service_type_id'])
                ->where('price_list_id', $data['price_list_id'])
                ->exists();

            if ($exists) return null;

            $cp = ClientPrice::create($data);

            // Registro inicial de ajuste para precios de lista 2026
            if ($data['price_list_id'] === $this->list2026->id) {
                PriceAdjustment::create([
                    'client_price_id' => $cp->id,
                    'old_price'       => $data['base_price'],
                    'new_price'       => $data['final_price'],
                    'reason'          => 'annual_adjust',
                    'notes'           => 'Generado en ajuste anual 2026 (9%)',
                    'created_by'      => $adminId,
                ]);
            }

            return $cp;
        };

        // ── EMPRESA DEMO S.A.S. ──────────────────────────
        $c1 = $clientMap['900123456'] ?? null;
        if ($c1 && $cert1 && $hab && $metered) {
            // Precio 2025
            $createPrice([
                'client_id' => $c1, 'service_type_id' => $cert1->id, 'price_list_id' => $l25->id,
                'base_price' => 146000, 'adjustment_percentage' => 0, 'final_price' => 146000,
                'applies_iva' => true, 'iva_percentage' => 19,
                'valid_from' => '2025-01-01', 'valid_until' => '2025-12-31',
            ]);
            // Precio 2026 (ajustado 9%)
            $cp = $createPrice([
                'client_id' => $c1, 'service_type_id' => $cert1->id, 'price_list_id' => $l26->id,
                'base_price' => 146000, 'adjustment_percentage' => 9,
                'final_price' => round(146000 * 1.09, 2),
                'applies_iva' => true, 'iva_percentage' => 19,
                'valid_from' => '2026-01-01',
            ]);
            // Precio habilitaciones 2026
            $createPrice([
                'client_id' => $c1, 'service_type_id' => $hab->id, 'price_list_id' => $l26->id,
                'base_price' => 85000, 'adjustment_percentage' => 9,
                'final_price' => round(85000 * 1.09, 2),
                'applies_iva' => false, 'iva_percentage' => 0,
                'valid_from' => '2026-01-01',
            ]);
            // Facturación electrónica ilimitada (metered)
            $createPrice([
                'client_id' => $c1, 'service_type_id' => $metered->id, 'price_list_id' => $l26->id,
                'base_price' => 180, 'adjustment_percentage' => 0,
                'negotiated_price' => 165, 'final_price' => 165,
                'applies_iva' => false, 'iva_percentage' => 0,
                'valid_from' => '2026-01-01',
                'notes' => 'Precio negociado por volumen. Tarifa fija por documento.',
            ]);
            // Simular una renegociación
            if ($cp) {
                PriceAdjustment::firstOrCreate(
                    ['client_price_id' => $cp->id, 'reason' => 'negotiation'],
                    [
                        'old_price'  => round(146000 * 1.09, 2),
                        'new_price'  => 152000,
                        'reason'     => 'negotiation',
                        'notes'      => 'Negociación directa con gerencia. Descuento por contrato multi-año.',
                        'created_by' => $adminId,
                    ]
                );
            }
        }

        // ── TECNOLOGÍA AVANZADA ──────────────────────────
        $c2 = $clientMap['800987654'] ?? null;
        if ($c2 && $cert1 && $cert2 && $metered) {
            $createPrice([
                'client_id' => $c2, 'service_type_id' => $cert1->id, 'price_list_id' => $l26->id,
                'base_price' => 146000, 'adjustment_percentage' => 9,
                'final_price' => round(146000 * 1.09, 2),
                'applies_iva' => true, 'iva_percentage' => 19,
                'valid_from' => '2026-01-01',
            ]);
            $createPrice([
                'client_id' => $c2, 'service_type_id' => $cert2->id, 'price_list_id' => $l26->id,
                'base_price' => 235000, 'adjustment_percentage' => 9,
                'final_price' => round(235000 * 1.09, 2),
                'applies_iva' => true, 'iva_percentage' => 19,
                'valid_from' => '2026-01-01',
                'duration_years' => 2,
            ]);
            $createPrice([
                'client_id' => $c2, 'service_type_id' => $metered->id, 'price_list_id' => $l26->id,
                'base_price' => 180, 'adjustment_percentage' => 9,
                'final_price' => round(180 * 1.09, 2),
                'applies_iva' => false, 'iva_percentage' => 0,
                'valid_from' => '2026-01-01',
            ]);
        }

        // ── CARLOS RAMÍREZ (natural) ──────────────────────────
        $c3 = $clientMap['1023456789'] ?? null;
        if ($c3 && $cert1 && $hab) {
            $createPrice([
                'client_id' => $c3, 'service_type_id' => $cert1->id, 'price_list_id' => $l26->id,
                'base_price' => 146000, 'adjustment_percentage' => 9,
                'final_price' => round(146000 * 1.09, 2),
                'applies_iva' => true, 'iva_percentage' => 19,
                'valid_from' => '2026-01-01',
            ]);
        }

        // ── INVERSIONES DEL VALLE ──────────────────────────
        $c4 = $clientMap['890654321'] ?? null;
        if ($c4 && $cert1 && $doc) {
            $createPrice([
                'client_id' => $c4, 'service_type_id' => $cert1->id, 'price_list_id' => $l26->id,
                'base_price' => 146000, 'adjustment_percentage' => 0,
                'negotiated_price' => 142000, 'final_price' => 142000,
                'applies_iva' => true, 'iva_percentage' => 19,
                'valid_from' => '2026-01-01',
                'notes' => 'Precio especial por acuerdo comercial 2025-2026.',
            ]);
            $createPrice([
                'client_id' => $c4, 'service_type_id' => $doc->id, 'price_list_id' => $l26->id,
                'base_price' => 28000, 'adjustment_percentage' => 9,
                'final_price' => round(28000 * 1.09, 2),
                'applies_iva' => false, 'iva_percentage' => 0,
                'valid_from' => '2026-01-01',
            ]);
        }

        // ── SERVICIOS LOGÍSTICOS ──────────────────────────
        $c5 = $clientMap['901234567'] ?? null;
        if ($c5 && $doc && $hab) {
            $createPrice([
                'client_id' => $c5, 'service_type_id' => $doc->id, 'price_list_id' => $l26->id,
                'base_price' => 28000, 'adjustment_percentage' => 9,
                'discount_percentage' => 5,
                'final_price' => round(28000 * 1.09 * 0.95, 2),
                'applies_iva' => false, 'iva_percentage' => 0,
                'valid_from' => '2026-01-01',
            ]);
            $createPrice([
                'client_id' => $c5, 'service_type_id' => $hab->id, 'price_list_id' => $l26->id,
                'base_price' => 85000, 'adjustment_percentage' => 9,
                'final_price' => round(85000 * 1.09, 2),
                'applies_iva' => false, 'iva_percentage' => 0,
                'valid_from' => '2026-01-01',
            ]);
        }

        // ── CONSTRUCTORA ANDINA ──────────────────────────
        $c7 = $clientMap['830456789'] ?? null;
        if ($c7 && $hab) {
            $createPrice([
                'client_id' => $c7, 'service_type_id' => $hab->id, 'price_list_id' => $l26->id,
                'base_price' => 85000, 'adjustment_percentage' => 9,
                'final_price' => round(85000 * 1.09, 2),
                'applies_iva' => false, 'iva_percentage' => 0,
                'valid_from' => '2026-01-01',
            ]);
        }

        // ── FARMACÉUTICA DEL NORTE ──────────────────────────
        $c8 = $clientMap['860789012'] ?? null;
        if ($c8 && $cert1 && $cert2) {
            $createPrice([
                'client_id' => $c8, 'service_type_id' => $cert1->id, 'price_list_id' => $l26->id,
                'base_price' => 146000, 'adjustment_percentage' => 9,
                'final_price' => round(146000 * 1.09, 2),
                'applies_iva' => true, 'iva_percentage' => 19,
                'valid_from' => '2026-01-01',
            ]);
            $createPrice([
                'client_id' => $c8, 'service_type_id' => $cert2->id, 'price_list_id' => $l26->id,
                'base_price' => 235000, 'adjustment_percentage' => 9,
                'final_price' => round(235000 * 1.09, 2),
                'applies_iva' => true, 'iva_percentage' => 19,
                'valid_from' => '2026-01-01',
                'duration_years' => 2,
            ]);
        }

        // ── COMERCIALIZADORA PACÍFICO ──────────────────────────
        $c10 = $clientMap['900567891'] ?? null;
        if ($c10 && $doc && $metered) {
            $createPrice([
                'client_id' => $c10, 'service_type_id' => $doc->id, 'price_list_id' => $l26->id,
                'base_price' => 28000, 'adjustment_percentage' => 9,
                'final_price' => round(28000 * 1.09, 2),
                'applies_iva' => false, 'iva_percentage' => 0,
                'valid_from' => '2026-01-01',
            ]);
            $createPrice([
                'client_id' => $c10, 'service_type_id' => $metered->id, 'price_list_id' => $l26->id,
                'base_price' => 180, 'adjustment_percentage' => 9,
                'final_price' => round(180 * 1.09, 2),
                'applies_iva' => false, 'iva_percentage' => 0,
                'valid_from' => '2026-01-01',
            ]);
        }
    }

    // ────────────────────────────────────────────────────────
    // 5. Bolsas de clientes y consumos
    // ────────────────────────────────────────────────────────
    private function seedClientBundles(): void
    {
        $bundleCert = ServiceType::where('name', 'Bolsa Certificados')->first();
        $bundleHab  = ServiceType::where('name', 'Bolsa Habilitaciones')->first();

        $clientMap = Client::whereIn('document_number', [
            '900123456', '800987654', '830456789', '860789012', '901234567',
        ])->pluck('id', 'document_number');

        $adminId = $this->adminUser->id;

        // Tiers disponibles
        $tier1000 = BundleTier::where('name', 'Bolsa 1.000')->where('price_list_id', $this->list2026->id)->first();
        $tier3000 = BundleTier::where('name', 'Bolsa 3.000')->where('price_list_id', $this->list2026->id)->first();
        $tier500  = BundleTier::where('name', 'Bolsa 500')->where('price_list_id', $this->list2026->id)->first();
        $tierHab  = BundleTier::where('name', 'Paquete 100 Hab.')->where('price_list_id', $this->list2026->id)->first();

        // ── EMPRESA DEMO: Bolsa 3.000 cert. (60% consumida) ──
        $c1 = $clientMap['900123456'] ?? null;
        if ($c1 && $tier3000) {
            $bundle1 = ClientBundle::firstOrCreate(
                ['client_id' => $c1, 'bundle_tier_id' => $tier3000->id, 'price_list_id' => $this->list2026->id],
                [
                    'quantity_purchased' => 3000,
                    'quantity_consumed'  => 0,
                    'price_paid'         => 375105,
                    'purchased_at'       => '2026-01-15',
                    'expires_at'         => '2026-12-31',
                    'is_active'          => true,
                ]
            );

            if ($bundle1->quantity_consumed === 0) {
                $consumptions = [
                    ['qty' => 450, 'desc' => 'Emisión enero 2026', 'ref' => 'FAC-2026-0001', 'date' => '2026-01-31'],
                    ['qty' => 620, 'desc' => 'Emisión febrero 2026', 'ref' => 'FAC-2026-0045', 'date' => '2026-02-28'],
                    ['qty' => 731, 'desc' => 'Emisión marzo 2026', 'ref' => 'FAC-2026-0102', 'date' => '2026-03-01'],
                ];
                foreach ($consumptions as $c) {
                    BundleConsumption::create([
                        'client_bundle_id' => $bundle1->id,
                        'quantity'         => $c['qty'],
                        'consumed_at'      => $c['date'],
                        'description'      => $c['desc'],
                        'reference'        => $c['ref'],
                        'created_by'       => $adminId,
                    ]);
                    $bundle1->increment('quantity_consumed', $c['qty']);
                }
                $bundle1->refresh();
            }
        }

        // ── TECNOLOGÍA AVANZADA: Bolsa 1.000 cert. (95% consumida — en riesgo) ──
        $c2 = $clientMap['800987654'] ?? null;
        if ($c2 && $tier1000) {
            $bundle2 = ClientBundle::firstOrCreate(
                ['client_id' => $c2, 'bundle_tier_id' => $tier1000->id, 'price_list_id' => $this->list2026->id],
                [
                    'quantity_purchased' => 1000,
                    'quantity_consumed'  => 0,
                    'price_paid'         => 317735,
                    'purchased_at'       => '2026-01-10',
                    'is_active'          => true,
                ]
            );

            if ($bundle2->quantity_consumed === 0) {
                $consumptions2 = [
                    ['qty' => 320, 'desc' => 'Lote enero', 'ref' => 'ORD-2026-0011', 'date' => '2026-01-20'],
                    ['qty' => 410, 'desc' => 'Lote febrero', 'ref' => 'ORD-2026-0067', 'date' => '2026-02-22'],
                    ['qty' => 225, 'desc' => 'Lote marzo',  'ref' => 'ORD-2026-0098', 'date' => '2026-03-01'],
                ];
                foreach ($consumptions2 as $c) {
                    BundleConsumption::create([
                        'client_bundle_id' => $bundle2->id,
                        'quantity'         => $c['qty'],
                        'consumed_at'      => $c['date'],
                        'description'      => $c['desc'],
                        'reference'        => $c['ref'],
                        'created_by'       => $adminId,
                    ]);
                    $bundle2->increment('quantity_consumed', $c['qty']);
                }
            }
        }

        // ── CONSTRUCTORA ANDINA: Paquete 100 Hab. (20% consumido) ──
        $c7 = $clientMap['830456789'] ?? null;
        if ($c7 && $tierHab) {
            $bundle3 = ClientBundle::firstOrCreate(
                ['client_id' => $c7, 'bundle_tier_id' => $tierHab->id, 'price_list_id' => $this->list2026->id],
                [
                    'quantity_purchased' => 100,
                    'quantity_consumed'  => 0,
                    'price_paid'         => 2200000,
                    'purchased_at'       => '2026-02-01',
                    'expires_at'         => '2026-07-31',
                    'is_active'          => true,
                    'notes'              => 'Habilitaciones para proyecto Torre Norte.',
                ]
            );

            if ($bundle3->quantity_consumed === 0) {
                BundleConsumption::create([
                    'client_bundle_id' => $bundle3->id,
                    'quantity'         => 20,
                    'consumed_at'      => '2026-02-15',
                    'description'      => 'Habilitaciones fase 1',
                    'reference'        => 'PROJ-2026-TN01',
                    'created_by'       => $adminId,
                ]);
                $bundle3->increment('quantity_consumed', 20);
            }
        }

        // ── FARMACÉUTICA: Bolsa 500 cert. (agotada — inactiva) ──
        $c8 = $clientMap['860789012'] ?? null;
        if ($c8 && $tier500) {
            $bundle4 = ClientBundle::firstOrCreate(
                ['client_id' => $c8, 'bundle_tier_id' => $tier500->id, 'price_list_id' => $this->list2025->id],
                [
                    'quantity_purchased' => 500,
                    'quantity_consumed'  => 500,
                    'price_paid'         => 180395,
                    'purchased_at'       => '2025-03-01',
                    'expires_at'         => '2025-12-31',
                    'is_active'          => false,
                    'notes'              => 'Bolsa 2025 agotada.',
                ]
            );
        }

        // ── SERVICIOS LOGÍSTICOS: Bolsa 500 cert. 2026 (nueva — 5% consumida) ──
        $c5 = $clientMap['901234567'] ?? null;
        if ($c5 && $tier500) {
            $bundle5 = ClientBundle::firstOrCreate(
                ['client_id' => $c5, 'bundle_tier_id' => $tier500->id, 'price_list_id' => $this->list2026->id],
                [
                    'quantity_purchased' => 500,
                    'quantity_consumed'  => 0,
                    'price_paid'         => 196630,
                    'purchased_at'       => '2026-02-20',
                    'is_active'          => true,
                ]
            );

            if ($bundle5->quantity_consumed === 0) {
                BundleConsumption::create([
                    'client_bundle_id' => $bundle5->id,
                    'quantity'         => 25,
                    'consumed_at'      => '2026-03-01',
                    'description'      => 'Primeros documentos del año',
                    'reference'        => 'LOG-2026-0001',
                    'created_by'       => $adminId,
                ]);
                $bundle5->increment('quantity_consumed', 25);
            }
        }
    }

    // ────────────────────────────────────────────────────────
    // 6. Usos mensuales (facturación metered)
    // ────────────────────────────────────────────────────────
    private function seedMonthlyUsages(): void
    {
        $metered = ServiceType::where('name', 'Facturación Electrónica Ilimitada')->first();
        if (! $metered) return;

        $adminId = $this->adminUser->id;

        $clientDocs = ['900123456', '800987654', '900567891'];
        $clients    = Client::whereIn('document_number', $clientDocs)->pluck('id', 'document_number');

        foreach ($clients as $doc => $clientId) {
            $doc = (string) $doc; // PDO may return numeric varchar as int
            $cp = ClientPrice::where('client_id', $clientId)
                ->where('service_type_id', $metered->id)
                ->first();

            if (! $cp) continue;

            $usages = match ($doc) {
                '900123456' => [
                    ['year' => 2025, 'month' => 10, 'count' => 4320],
                    ['year' => 2025, 'month' => 11, 'count' => 3980],
                    ['year' => 2025, 'month' => 12, 'count' => 4150],
                    ['year' => 2026, 'month' => 1,  'count' => 4680],
                    ['year' => 2026, 'month' => 2,  'count' => 3870],
                ],
                '800987654' => [
                    ['year' => 2026, 'month' => 1, 'count' => 12450],
                    ['year' => 2026, 'month' => 2, 'count' => 11800],
                ],
                '900567891' => [
                    ['year' => 2026, 'month' => 1, 'count' => 890],
                    ['year' => 2026, 'month' => 2, 'count' => 1020],
                ],
                default => [],
            };

            foreach ($usages as $u) {
                MonthlyUsage::firstOrCreate(
                    [
                        'client_price_id' => $cp->id,
                        'period_year'     => $u['year'],
                        'period_month'    => $u['month'],
                    ],
                    [
                        'client_id'      => $clientId,
                        'document_count' => $u['count'],
                        'unit_price'     => $cp->final_price,
                        'total_amount'   => round($cp->final_price * $u['count'], 2),
                        'notes'          => null,
                        'created_by'     => $adminId,
                    ]
                );
            }
        }
    }
}
