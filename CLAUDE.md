# SPEC: Sistema de Gestión de Precios y Servicios

## Objetivo

Construir una aplicación web para gestionar precios de servicios (certificados, habilitaciones, documentos y bolsas/paquetes) por cliente, con soporte para ajustes anuales, renegociaciones y consumo de bolsas prepagadas. Todo orientado a la realidad de una empresa colombiana que factura a otras empresas y personas naturales.

---

## Stack Tecnológico

- **Backend:** Laravel 12, PHP 8.3+
- **Frontend:** Svelte 5 + Inertia.js
- **Base de datos:** PostgreSQL
- **CSS:** Bootstrap 5
- **Autenticación:** Laravel Breeze (Inertia/Svelte)
- **Permisos:** Spatie Laravel Permission
- **Otros:** Laravel Pest (tests), Laravel Pint (linting)

---

## Diagrama ER

```
┌──────────────────────────────┐
│           clients            │
├──────────────────────────────┤
│ id (PK)                      │
│ type: ENUM(natural, juridica)│
│ business_name (razón social) │
│ trade_name (nombre comercial)│
│ nit / cedula                 │
│ dv (dígito verificación)     │
│ tax_regime: ENUM(simple,     │
│   ordinario)                 │
│ tax_responsibility: JSON     │  ← R-99, O-13, O-15, etc. (DIAN)
│ ciiu_code                    │
│ email                        │
│ email_billing                │
│ phone                        │
│ mobile                       │
│ address                      │
│ city                         │
│ department                   │
│ country (default: CO)        │
│ postal_code                  │
│ is_active (bool)             │
│ notes                        │
│ timestamps                   │
└──────────┬───────────────────┘
           │ 1:N
           ▼
┌──────────────────────────────┐       ┌───────────────────────────────┐
│       client_prices          │       │          price_lists          │
├──────────────────────────────┤       ├───────────────────────────────┤
│ id (PK)                      │       │ id (PK)                       │
│ client_id (FK)               │       │ year (int, unique)            │
│ service_type_id (FK) ────────┼──┐    │ name (ej: "Lista 2026")       │
│ price_list_id (FK) ──────────┼──┼───►│ adjustment_percentage (dec)   │
│ duration_years (1 o 2)       │  │    │ is_active (bool)              │
│ base_price (precio año ant.) │  │    │ notes                         │
│ adjustment_percentage (dec)  │  │    │ timestamps                    │
│ negotiated_price (nullable)  │  │    └───────────────────────────────┘
│ discount_percentage (nullable│  │
│ final_price (generated/calc) │  │    ┌───────────────────────────────┐
│ applies_iva (bool)           │  └───►│        service_types          │
│ iva_percentage (dec, def 19) │       ├───────────────────────────────┤
│ notes                        │       │ id (PK)                       │
│ valid_from (date)            │       │ name (Certificado, Habilitac.)│
│ valid_until (date, nullable) │       │ billing_type: ENUM(unit,      │
│ timestamps                   │       │   bundle)                     │
└──────────┬───────────────────┘       │ applies_iva (bool default)    │
           │                           │ iva_percentage (dec, def 19)  │
           │                           │ description                   │
           │                           │ is_active (bool)              │
           │                           │ timestamps                    │
           │                           └──────────┬────────────────────┘
           │                                      │ 1:N
           │                                      ▼
           │                           ┌───────────────────────────────┐
           │                           │        bundle_tiers           │
           │                           ├───────────────────────────────┤
           │                           │ id (PK)                       │
           │                           │ service_type_id (FK)          │
           │                           │ price_list_id (FK)            │
           │                           │ name (ej: "Bolsa 500")        │
           │                           │ quantity (int)                │
           │                           │ price (decimal)               │
           │                           │ unit_price (generated)        │
           │                           │ is_active (bool)              │
           │                           │ timestamps                    │
           │                           └───────────────────────────────┘
           │
           ▼ (cuando billing_type = bundle)
┌──────────────────────────────┐
│       client_bundles         │
├──────────────────────────────┤
│ id (PK)                      │
│ client_id (FK)               │
│ bundle_tier_id (FK)          │
│ price_list_id (FK)           │
│ quantity_purchased (int)     │
│ quantity_consumed (int)      │
│ quantity_remaining (virtual) │
│ price_paid (decimal)         │
│ purchased_at (date)          │
│ expires_at (date, nullable)  │
│ is_active (bool)             │
│ notes                        │
│ timestamps                   │
└──────────┬───────────────────┘
           │ 1:N
           ▼
┌──────────────────────────────┐
│    bundle_consumptions       │
├──────────────────────────────┤
│ id (PK)                      │
│ client_bundle_id (FK)        │
│ quantity (int)               │
│ consumed_at (datetime)       │
│ description                  │
│ reference (nro factura, etc.)│
│ created_by (FK users)        │
│ timestamps                   │
└──────────────────────────────┘

┌──────────────────────────────┐
│       price_adjustments      │  ← historial de cambios de precio
├──────────────────────────────┤
│ id (PK)                      │
│ client_price_id (FK)         │
│ old_price                    │
│ new_price                    │
│ reason: ENUM(annual_adjust,  │
│   negotiation, correction)   │
│ notes                        │
│ created_by (FK users)        │
│ timestamps                   │
└──────────────────────────────┘
```

---

## Estructura de Módulos

```
app/
├── Models/
│   ├── Client.php
│   ├── ServiceType.php
│   ├── PriceList.php
│   ├── BundleTier.php
│   ├── ClientPrice.php
│   ├── ClientBundle.php
│   ├── BundleConsumption.php
│   └── PriceAdjustment.php
│
├── Http/Controllers/
│   ├── ClientController.php
│   ├── ServiceTypeController.php
│   ├── PriceListController.php
│   ├── BundleTierController.php
│   ├── ClientPriceController.php
│   ├── ClientBundleController.php
│   ├── BundleConsumptionController.php
│   └── DashboardController.php
│
├── Services/
│   ├── PriceCalculatorService.php   ← lógica de ajuste, IVA, negociación
│   ├── BundleService.php            ← lógica de consumo y saldo
│   └── PriceListGeneratorService.php ← generar nueva lista desde anterior
│
├── DTOs/
│   ├── ClientPriceDTO.php
│   ├── BundleStatusDTO.php
│   └── PriceListSummaryDTO.php
│
└── Policies/
    ├── ClientPolicy.php
    └── PriceListPolicy.php

resources/js/
├── Pages/
│   ├── Dashboard/
│   │   └── Index.svelte
│   ├── Clients/
│   │   ├── Index.svelte
│   │   ├── Create.svelte
│   │   ├── Edit.svelte
│   │   └── Show.svelte          ← vista detalle con precios + bolsas
│   ├── PriceLists/
│   │   ├── Index.svelte
│   │   ├── Create.svelte
│   │   └── Show.svelte          ← con bundle tiers embebidos
│   ├── ServiceTypes/
│   │   └── Index.svelte
│   ├── ClientPrices/
│   │   ├── Index.svelte         ← listado global con filtros
│   │   └── Edit.svelte          ← formulario negociación
│   └── ClientBundles/
│       ├── Show.svelte          ← saldo + consumos
│       └── Consume.svelte       ← registrar consumo
│
└── Components/
    ├── PriceBadge.svelte
    ├── BundleProgressBar.svelte
    └── ClientTypeTag.svelte
```

---

## Detalle de Migraciones

### 1. `create_clients_table`

```php
Schema::create('clients', function (Blueprint $table) {
    $table->id();
    $table->enum('type', ['natural', 'juridica'])->default('juridica');
    $table->string('business_name');                    // Razón social o nombre completo
    $table->string('trade_name')->nullable();           // Nombre comercial
    $table->string('document_number')->unique();        // NIT o Cédula
    $table->string('dv', 1)->nullable();               // Dígito de verificación (NIT)
    $table->enum('tax_regime', ['simple', 'ordinario'])->default('ordinario');
    $table->json('tax_responsibilities')->nullable();   // Ej: ["R-99-PN", "O-13", "O-15"]
    $table->string('ciiu_code')->nullable();            // Código CIIU actividad económica
    $table->string('email')->nullable();
    $table->string('email_billing')->nullable();        // Email para envío de facturas
    $table->string('phone')->nullable();
    $table->string('mobile')->nullable();
    $table->text('address')->nullable();
    $table->string('city')->nullable();
    $table->string('department')->nullable();
    $table->string('country', 2)->default('CO');
    $table->string('postal_code')->nullable();
    $table->boolean('is_active')->default(true);
    $table->text('notes')->nullable();
    $table->timestamps();
    $table->softDeletes();
});
```

### 2. `create_service_types_table`

```php
Schema::create('service_types', function (Blueprint $table) {
    $table->id();
    $table->string('name');                              // Certificado, Habilitación, Documento, Paquete
    $table->enum('billing_type', ['unit', 'bundle'])->default('unit');
    $table->boolean('applies_iva')->default(false);
    $table->decimal('iva_percentage', 5, 2)->default(19.00);
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### 3. `create_price_lists_table`

```php
Schema::create('price_lists', function (Blueprint $table) {
    $table->id();
    $table->year('year')->unique();
    $table->string('name');
    $table->decimal('adjustment_percentage', 5, 2)->default(0);
    $table->boolean('is_active')->default(false);
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### 4. `create_bundle_tiers_table`

```php
Schema::create('bundle_tiers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('service_type_id')->constrained()->restrictOnDelete();
    $table->foreignId('price_list_id')->constrained()->restrictOnDelete();
    $table->string('name');                              // "Bolsa 500", "Paquete 10.000"
    $table->integer('quantity');
    $table->decimal('price', 15, 2);
    $table->decimal('unit_price', 15, 4)->storedAs('price / quantity');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### 5. `create_client_prices_table`

```php
Schema::create('client_prices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('client_id')->constrained()->cascadeOnDelete();
    $table->foreignId('service_type_id')->constrained()->restrictOnDelete();
    $table->foreignId('price_list_id')->constrained()->restrictOnDelete();
    $table->unsignedTinyInteger('duration_years')->nullable();   // 1 o 2
    $table->decimal('base_price', 15, 2);                        // Precio año anterior
    $table->decimal('adjustment_percentage', 5, 2)->nullable();  // Si difiere del global
    $table->decimal('negotiated_price', 15, 2)->nullable();      // Override manual
    $table->decimal('discount_percentage', 5, 2)->nullable();    // Descuento adicional
    $table->decimal('final_price', 15, 2);                       // Precio final a cobrar
    $table->boolean('applies_iva')->default(false);
    $table->decimal('iva_percentage', 5, 2)->default(19.00);
    $table->date('valid_from');
    $table->date('valid_until')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->unique(['client_id', 'service_type_id', 'price_list_id', 'duration_years']);
});
```

### 6. `create_client_bundles_table`

```php
Schema::create('client_bundles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('client_id')->constrained()->restrictOnDelete();
    $table->foreignId('bundle_tier_id')->constrained()->restrictOnDelete();
    $table->foreignId('price_list_id')->constrained()->restrictOnDelete();
    $table->integer('quantity_purchased');
    $table->integer('quantity_consumed')->default(0);
    $table->decimal('price_paid', 15, 2);
    $table->date('purchased_at');
    $table->date('expires_at')->nullable();
    $table->boolean('is_active')->default(true);
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### 7. `create_bundle_consumptions_table`

```php
Schema::create('bundle_consumptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('client_bundle_id')->constrained()->restrictOnDelete();
    $table->integer('quantity');
    $table->timestamp('consumed_at');
    $table->string('description')->nullable();
    $table->string('reference')->nullable();             // Nro factura, orden, etc.
    $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
    $table->timestamps();
});
```

### 8. `create_price_adjustments_table`

```php
Schema::create('price_adjustments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('client_price_id')->constrained()->cascadeOnDelete();
    $table->decimal('old_price', 15, 2);
    $table->decimal('new_price', 15, 2);
    $table->enum('reason', ['annual_adjust', 'negotiation', 'correction'])->default('annual_adjust');
    $table->text('notes')->nullable();
    $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
    $table->timestamps();
});
```

---

## Modelos y Relaciones Clave

### Client.php

```php
class Client extends Model
{
    use SoftDeletes;

    protected $casts = [
        'tax_responsibilities' => 'array',
        'is_active' => 'boolean',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(ClientPrice::class);
    }

    public function bundles(): HasMany
    {
        return $this->hasMany(ClientBundle::class);
    }

    public function activeBundles(): HasMany
    {
        return $this->bundles()->where('is_active', true);
    }

    public function currentPrices(): HasMany
    {
        return $this->prices()->whereHas('priceList', fn($q) => $q->where('is_active', true));
    }

    // NIT formateado: 900.123.456-7
    public function getFormattedNitAttribute(): string
    {
        if ($this->type === 'juridica' && $this->dv) {
            return number_format($this->document_number, 0, ',', '.') . '-' . $this->dv;
        }
        return $this->document_number;
    }
}
```

### PriceCalculatorService.php

```php
class PriceCalculatorService
{
    /**
     * Calcula el precio final a partir del precio base + ajuste + negociación
     */
    public function calculate(
        float $basePrice,
        float $adjustmentPercentage,
        ?float $negotiatedPrice = null,
        ?float $discountPercentage = null
    ): float {
        if ($negotiatedPrice !== null) {
            $price = $negotiatedPrice;
        } else {
            $price = $basePrice * (1 + $adjustmentPercentage / 100);
        }

        if ($discountPercentage !== null) {
            $price = $price * (1 - $discountPercentage / 100);
        }

        return round($price, 2);
    }

    public function withIva(float $price, float $ivaPercentage = 19.00): float
    {
        return round($price * (1 + $ivaPercentage / 100), 2);
    }
}
```

### BundleService.php

```php
class BundleService
{
    public function consume(ClientBundle $bundle, int $quantity, string $description = '', string $reference = ''): BundleConsumption
    {
        if ($bundle->quantity_remaining < $quantity) {
            throw new \Exception("Saldo insuficiente en la bolsa. Disponible: {$bundle->quantity_remaining}");
        }

        DB::transaction(function () use ($bundle, $quantity, $description, $reference) {
            $consumption = $bundle->consumptions()->create([
                'quantity'    => $quantity,
                'consumed_at' => now(),
                'description' => $description,
                'reference'   => $reference,
                'created_by'  => auth()->id(),
            ]);

            $bundle->increment('quantity_consumed', $quantity);

            if ($bundle->quantity_remaining === 0) {
                $bundle->update(['is_active' => false]);
            }

            return $consumption;
        });
    }

    public function getRemainingBalance(ClientBundle $bundle): int
    {
        return $bundle->quantity_purchased - $bundle->quantity_consumed;
    }
}
```

---

## Rutas (routes/web.php)

```php
Route::middleware(['auth'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Clientes
    Route::resource('clients', ClientController::class);
    Route::get('clients/{client}/prices', [ClientPriceController::class, 'clientPrices'])->name('clients.prices');
    Route::get('clients/{client}/bundles', [ClientBundleController::class, 'clientBundles'])->name('clients.bundles');

    // Listas de precios
    Route::resource('price-lists', PriceListController::class);
    Route::post('price-lists/{priceList}/generate-from-previous', [PriceListController::class, 'generateFromPrevious'])
        ->name('price-lists.generate');
    Route::post('price-lists/{priceList}/activate', [PriceListController::class, 'activate'])
        ->name('price-lists.activate');

    // Tipos de servicio
    Route::resource('service-types', ServiceTypeController::class)->except(['show']);

    // Bundle tiers (dentro de una lista)
    Route::resource('price-lists.bundle-tiers', BundleTierController::class)->shallow();

    // Precios por cliente
    Route::resource('client-prices', ClientPriceController::class)->except(['index', 'show']);
    Route::get('client-prices', [ClientPriceController::class, 'index'])->name('client-prices.index');

    // Bolsas
    Route::resource('client-bundles', ClientBundleController::class)->except(['index']);
    Route::post('client-bundles/{clientBundle}/consume', [BundleConsumptionController::class, 'store'])
        ->name('client-bundles.consume');
    Route::get('client-bundles/{clientBundle}/consumptions', [BundleConsumptionController::class, 'index'])
        ->name('client-bundles.consumptions');
});
```

---

## Seeders

Crear los siguientes seeders para datos iniciales:

### ServiceTypeSeeder

```php
ServiceType::insert([
    ['name' => 'Certificado 1 año',   'billing_type' => 'unit',   'applies_iva' => true,  'iva_percentage' => 19.00],
    ['name' => 'Certificado 2 años',  'billing_type' => 'unit',   'applies_iva' => true,  'iva_percentage' => 19.00],
    ['name' => 'Habilitación',        'billing_type' => 'unit',   'applies_iva' => false, 'iva_percentage' => 0],
    ['name' => 'Documento',           'billing_type' => 'unit',   'applies_iva' => false, 'iva_percentage' => 0],
    ['name' => 'Bolsa Certificados',  'billing_type' => 'bundle', 'applies_iva' => true,  'iva_percentage' => 19.00],
    ['name' => 'Bolsa Documentos',    'billing_type' => 'bundle', 'applies_iva' => false, 'iva_percentage' => 0],
]);
```

### PriceListSeeder

```php
$list = PriceList::create([
    'year'                  => 2026,
    'name'                  => 'Lista de Precios 2026',
    'adjustment_percentage' => 9.00,
    'is_active'             => true,
]);

// Bundle tiers estándar para Bolsa Certificados
$bundleCert = ServiceType::where('name', 'Bolsa Certificados')->first();
foreach ([
    ['name' => 'Bolsa 500',     'quantity' => 500,   'price' => 196630],
    ['name' => 'Bolsa 1.000',   'quantity' => 1000,  'price' => 317735],
    ['name' => 'Bolsa 3.000',   'quantity' => 3000,  'price' => 375105],
    ['name' => 'Bolsa 5.000',   'quantity' => 5000,  'price' => 433275],
    ['name' => 'Bolsa 7.000',   'quantity' => 7000,  'price' => 479711],
    ['name' => 'Paquete 10.000','quantity' => 10000, 'price' => 623916],
    ['name' => 'Paquete 15.000','quantity' => 15000, 'price' => 779895],
] as $tier) {
    BundleTier::create(array_merge($tier, [
        'service_type_id' => $bundleCert->id,
        'price_list_id'   => $list->id,
    ]));
}
```

---

## Responsabilidades fiscales DIAN (tax_responsibilities)

Usar como referencia para el campo `tax_responsibilities` (JSON array de strings):

| Código | Descripción |
|--------|-------------|
| O-13   | Gran contribuyente |
| O-15   | Autorretenedor |
| O-23   | Agente de retención IVA |
| O-47   | Régimen simple de tributación |
| R-99-PN | No aplica (Persona Natural no responsable) |

---

## Dashboard: métricas a mostrar

1. **Clientes activos** — total y desglose por tipo (natural / jurídica)
2. **Precios vigentes** — cuántos clientes tienen precio en la lista activa
3. **Sin precio asignado** — clientes activos sin precio en la lista actual (alerta)
4. **Bolsas activas** — listado con % de consumo (barra de progreso)
5. **Bolsas en riesgo** — bolsas con < 10% de saldo restante
6. **Comparativo de precios** — tabla resumen precio 2025 vs 2026 por tipo de servicio

---

## Funcionalidad: Generación de nueva lista de precios

Al crear una nueva lista de precios, ofrecer la opción de **"Generar desde lista anterior"**:

1. Toma todos los `client_prices` de la lista anterior
2. Aplica el nuevo `adjustment_percentage` a cada `base_price`
3. Crea nuevos registros en `client_prices` con:
   - `base_price` = `final_price` de la lista anterior
   - `final_price` = calculado con el nuevo ajuste
   - `negotiated_price` = null (parte limpio para renegociación)
4. Copia los `bundle_tiers` con precios ajustados
5. Registra en `price_adjustments` con `reason = 'annual_adjust'`

---

## Validaciones importantes

- Un cliente no puede tener dos precios para el mismo `service_type` + `price_list` + `duration_years`
- El `negotiated_price` al guardarse debe registrar automáticamente un `PriceAdjustment` con `reason = 'negotiation'`
- Al registrar un consumo de bolsa, verificar que `quantity_remaining >= quantity`
- El `final_price` debe recalcularse automáticamente en el observer del modelo cuando cambien `base_price`, `adjustment_percentage`, `negotiated_price` o `discount_percentage`

---

## Observers a crear

- `ClientPriceObserver` — recalcula `final_price` en `saving`, registra `PriceAdjustment` en `updated`
- `ClientBundleObserver` — marca `is_active = false` automáticamente cuando `quantity_remaining = 0`

---

## Notas de implementación

- Usar **Inertia shared data** para pasar el `price_list` activo a todas las páginas (útil para el selector de año)
- Todos los precios en la BD se almacenan en **pesos colombianos (COP)** como enteros o con 2 decimales
- Los precios en las vistas se formatean con `number_format` colombiano: `$ 146.000`
- El campo `tax_responsibilities` usa un **multiselect** en el formulario con los códigos DIAN
- El campo `dv` solo aplica y se muestra cuando `type = 'juridica'`
- Implementar un **componente Svelte reutilizable** `<PriceInput>` que formatee automáticamente el valor en COP al escribir

---

## Comandos para iniciar

```bash
# Instalar dependencias
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Base de datos
php artisan migrate --seed

# Instalar Breeze con Inertia/Svelte
php artisan breeze:install svelte-inertia

# Instalar Spatie Permission
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate

# Dev server
composer run dev
```

---

## Roles iniciales (Spatie)

| Rol | Permisos |
|-----|----------|
| `admin` | Todo |
| `operator` | Ver y editar clientes, precios, registrar consumos. No puede crear/eliminar listas de precios |
| `viewer` | Solo lectura |

Crear estos roles en el `RoleSeeder` y asignar `admin` al primer usuario.
