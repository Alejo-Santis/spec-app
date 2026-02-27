# SPEC — Sistema de Gestión de Precios y Servicios

Aplicación web interna para gestionar precios de servicios (certificados, habilitaciones, documentos y bolsas/paquetes) por cliente, con soporte para ajustes anuales, renegociaciones y consumo de bolsas prepagadas. Orientada a empresas colombianas que facturan a otras empresas y personas naturales.

---

## Stack Tecnológico

| Capa | Tecnología | Versión |
|------|-----------|---------|
| Backend | Laravel | 12.53 |
| Runtime | PHP | 8.3 |
| Frontend | Svelte | 5.x |
| SPA Bridge | Inertia.js | 2.x |
| Base de datos | PostgreSQL | — |
| CSS / UI | Bootstrap 5 + Mantis Admin Template | — |
| Autenticación | Laravel Breeze (Inertia/Svelte) | — |
| Permisos | Spatie Laravel Permission | 7.x |
| Import / Export | Maatwebsite Excel | 3.1 |
| Build | Vite | 7.x |

---

## Funcionalidades implementadas

### Gestión de Clientes
- CRUD completo (alta, edición, baja lógica con `SoftDeletes`)
- Soporte para persona **natural** y persona **jurídica** (NIT con dígito verificación)
- Campos fiscales DIAN: régimen tributario, responsabilidades tributarias (JSON multiselect), código CIIU
- Paginación, búsqueda en tiempo real y filtros por tipo y estado
- **Importación masiva** desde CSV/Excel con template descargable
- **Exportación completa** a Excel

### Listas de Precios
- CRUD de listas anuales con porcentaje de ajuste global
- Activación de una lista como vigente (única activa a la vez)
- **Generación automática** desde lista anterior con ajuste proporcional aplicado
- Historial de cambios de precio (`price_adjustments`)
- Gestión de **bundle tiers** (bolsas/paquetes) por lista y tipo de servicio

### Tipos de Servicio
- CRUD: nombre, tipo de facturación (`unit` / `bundle`), IVA configurable
- Indicador activo/inactivo

### Precios por Cliente
- Asignación de precio por: cliente × servicio × lista de precios
- Fórmula: `base_price × (1 + ajuste%) → precio negociado (override) → descuento adicional`
- Soporte para vigencia de 1 o 2 años
- Vista global con filtros por lista, cliente y tipo de servicio
- **Importación masiva** desde CSV/Excel con template descargable
- **Exportación a Excel** (respeta el filtro de lista de precios activa)

### Bolsas / Paquetes Prepagados
- Asignación de bolsa a cliente desde los tiers configurados en la lista de precios
- Tracking de saldo: `cantidad_comprada − consumido = saldo_restante`
- Registro de consumos individuales (cantidad, descripción, referencia de factura)
- Historial completo de consumos con usuario que registró
- Indicadores visuales de riesgo: saldo < 10% → alerta roja
- **Exportación a Excel**

### Dashboard
- Métricas en tiempo real: clientes activos, cobertura de precios asignados, alertas
- Tabla de bolsas activas con barras de progreso (verde / amarillo / rojo por umbral)
- Panel de acciones rápidas (nuevo cliente, asignar precio, nueva bolsa, nueva lista)
- Lista de bolsas críticas (< 10% de saldo)
- Badge de lista de precios vigente visible en header y footer

---

## Interfaz de Usuario

### Layout (AppLayout)
- **Sidebar con modo mini**: colapsa a 70px mostrando solo iconos con tooltips al hover
  - Logotipo intercambiable (completo ↔ favicon SVG)
  - Footer con avatar del usuario, nombre, rol y botón de logout
- **Búsqueda global** (`Ctrl+K` / `Cmd+K`): modal con resultados en tiempo real, navegación por teclado (↑ ↓ Enter Esc), accesos rápidos cuando no hay query
- **Dropdown de usuario**: Svelte-managed (no depende de Bootstrap JS), se cierra al hacer click fuera
- **Notificaciones flash** automáticas con SweetAlert2

### Componentes reutilizables
| Componente | Descripción |
|-----------|-------------|
| `SearchModal` | Modal de búsqueda global con debounce 220ms y accesos rápidos |
| `FlashNotification` | Notificaciones de éxito/error/advertencia via SweetAlert2 |
| `ConfirmDelete` | Diálogo de confirmación antes de eliminar |
| `Pagination` | Paginación estilo Inertia (preserva filtros activos) |
| `PriceInput` | Input numérico con formato COP automático |
| `ClientForm` | Formulario compartido entre Create y Edit de clientes |

---

## Instalación

### Requisitos previos
- PHP 8.3+
- PostgreSQL 14+
- Node.js 18+ con npm
- Composer 2

### Paso a paso

```bash
# 1. Instalar dependencias
composer install
npm install

# 2. Configurar entorno
cp .env.example .env
php artisan key:generate
```

Editar `.env` con las credenciales de PostgreSQL:

```env
APP_NAME="SPEC"
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=spec_db
DB_USERNAME=spec_user
DB_PASSWORD=secret

SESSION_DRIVER=database
CACHE_STORE=database
```

```bash
# 3. Migrar base de datos y sembrar datos iniciales
php artisan migrate --seed

# 4. Compilar assets para producción
npm run build
```

### Servidor de desarrollo

```bash
# Levanta Laravel + Vite en paralelo (hot reload)
composer run dev
```

Acceder en: **http://localhost:8000**

**Credenciales por defecto:**
- Email: `admin@spec.co`
- Password: `password`

---

## Estructura del Proyecto

```
app/
├── Exports/
│   ├── ClientExport.php           # Exportar clientes a Excel
│   ├── ClientPriceExport.php      # Exportar precios (filtrable por lista)
│   └── ClientBundleExport.php     # Exportar bolsas activas
├── Http/Controllers/
│   ├── DashboardController.php
│   ├── ClientController.php
│   ├── ClientPriceController.php
│   ├── ClientBundleController.php
│   ├── BundleConsumptionController.php
│   ├── PriceListController.php
│   ├── BundleTierController.php
│   ├── ServiceTypeController.php
│   ├── ImportExportController.php  ← Import/Export masivo
│   └── SearchController.php        ← Búsqueda global (JSON)
├── Imports/
│   ├── ClientImport.php            # Importar clientes desde CSV/Excel
│   └── ClientPriceImport.php       # Importar precios desde CSV/Excel
├── Models/
│   ├── Client.php
│   ├── ClientBundle.php
│   ├── ClientPrice.php
│   ├── BundleConsumption.php
│   ├── BundleTier.php
│   ├── PriceAdjustment.php
│   ├── PriceList.php
│   └── ServiceType.php
└── Services/
    ├── PriceCalculatorService.php   # Cálculo de precios con ajuste/negociación/IVA
    └── BundleService.php            # Lógica de consumo y saldo de bolsas

resources/js/
├── Components/
│   ├── ClientForm.svelte
│   ├── ConfirmDelete.svelte
│   ├── FlashNotification.svelte
│   ├── Pagination.svelte
│   ├── PriceInput.svelte
│   └── SearchModal.svelte
├── Layouts/
│   ├── AppLayout.svelte            # Layout principal (sidebar, header, búsqueda)
│   └── AuthLayout.svelte           # Layout de login
└── Pages/
    ├── Auth/Login.svelte
    ├── Dashboard/Index.svelte
    ├── Clients/{Index,Create,Edit,Show}.svelte
    ├── ClientPrices/{Index,Create,Edit,ClientPrices}.svelte
    ├── ClientBundles/{Create,Show,Consumptions}.svelte
    ├── PriceLists/{Index,Create,Show}.svelte
    └── ServiceTypes/Index.svelte

public/csv/
├── template_clientes.csv           # Template para importación masiva de clientes
└── template_precios_clientes.csv   # Template para importación masiva de precios
```

---

## Endpoints principales

### Clientes
| Método | Ruta | Descripción |
|--------|------|-------------|
| `GET` | `/clients` | Listado con filtros y paginación |
| `GET` | `/clients/create` | Formulario alta |
| `POST` | `/clients` | Crear cliente |
| `GET` | `/clients/{id}` | Detalle con precios y bolsas |
| `PUT` | `/clients/{id}` | Actualizar |
| `DELETE` | `/clients/{id}` | Baja lógica |
| `GET` | `/clients/export` | Descargar Excel |
| `POST` | `/clients/import` | Subir CSV/Excel |
| `GET` | `/clients/template` | Descargar template CSV |

### Precios por Cliente
| Método | Ruta | Descripción |
|--------|------|-------------|
| `GET` | `/client-prices` | Listado global con filtros |
| `POST` | `/client-prices` | Asignar precio |
| `PUT` | `/client-prices/{id}` | Actualizar (renegociación) |
| `DELETE` | `/client-prices/{id}` | Eliminar |
| `GET` | `/client-prices/export` | Excel (`?price_list_id=N`) |
| `POST` | `/client-prices/import` | Importar masivo |
| `GET` | `/client-prices/template` | Descargar template CSV |

### Bolsas
| Método | Ruta | Descripción |
|--------|------|-------------|
| `POST` | `/client-bundles` | Crear bolsa |
| `GET` | `/client-bundles/{id}` | Detalle + saldo |
| `PUT` | `/client-bundles/{id}` | Actualizar |
| `POST` | `/client-bundles/{id}/consume` | Registrar consumo |
| `GET` | `/client-bundles/{id}/consumptions` | Historial de consumos |
| `GET` | `/client-bundles/export` | Descargar Excel |

### Utilidades
| Método | Ruta | Descripción |
|--------|------|-------------|
| `GET` | `/search?q=` | Búsqueda global (responde JSON) |
| `GET` | `/` | Dashboard |

---

## Importación masiva — Formato de archivos

### Clientes (`public/csv/template_clientes.csv`)

| Columna | Req. | Valores válidos |
|---------|------|----------------|
| `tipo` | ✓ | `juridica` / `natural` |
| `razon_social_nombre` | ✓ | Texto |
| `documento` | ✓ | NIT o cédula (se omiten duplicados) |
| `nombre_comercial` | | Texto |
| `dv` | | Dígito verificación NIT |
| `regimen` | | `ordinario` / `simple` |
| `email` | | Email |
| `email_facturacion` | | Email |
| `telefono` / `celular` | | Texto |
| `direccion` / `ciudad` / `departamento` | | Texto |
| `pais` | | Código 2 letras (por defecto `CO`) |
| `cod_postal` / `notas` | | Texto |

### Precios por Cliente (`public/csv/template_precios_clientes.csv`)

| Columna | Req. | Descripción |
|---------|------|-------------|
| `documento_cliente` | ✓ | NIT/cédula del cliente (debe existir) |
| `tipo_de_servicio` | ✓ | Nombre exacto del tipo de servicio |
| `lista_de_precios` | ✓ | Nombre exacto de la lista de precios |
| `precio_base` | ✓ | Número (COP) |
| `valido_desde` | ✓ | Fecha `YYYY-MM-DD` |
| `anios_vigencia` | | `1` o `2` |
| `ajuste_porcentaje` | | Número (ej: `9` para 9%) |
| `precio_negociado` | | Número — sobrescribe el cálculo automático |
| `descuento_porcentaje` | | Número (ej: `5` para 5%) |
| `aplica_iva` | | `Si` / `No` |
| `iva_porcentaje` | | Número (por defecto `19`) |
| `valido_hasta` | | Fecha `YYYY-MM-DD` |
| `notas` | | Texto |

> Las combinaciones cliente+servicio+lista ya existentes son omitidas automáticamente.

---

## Roles del sistema

| Rol | Permisos |
|-----|---------|
| `admin` | Acceso total a todas las funciones |
| `operator` | Ver y editar clientes, precios y consumos de bolsas. Sin crear/eliminar listas de precios |
| `viewer` | Solo lectura en todas las secciones |

Los roles se gestionan con **Spatie Laravel Permission** y se asignan desde el seeder inicial.

---

## Comandos de mantenimiento

```bash
# Refresh completo (⚠️ elimina todos los datos)
php artisan migrate:fresh --seed

# Limpiar caches de producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Linting PHP (Laravel Pint)
./vendor/bin/pint

# Ejecutar tests
php artisan test
```

---

## Notas técnicas

- Los precios se almacenan en **pesos colombianos (COP)** con 2 decimales, y se muestran en formato `$ 146.000`
- El campo `tax_responsibilities` se guarda como JSON array de códigos DIAN (ej: `["R-99-PN", "O-13"]`)
- Los formularios usan `useForm` de `@inertiajs/svelte` — no se usa `fetch()` directo
- El footer del sidebar requiere flex-column en `.navbar-wrapper` porque Mantis usa `height: calc(100vh - 60px)` en `.navbar-content`, lo que sin flex empujaría el footer fuera del viewport bajo `overflow: hidden`
- Los tooltips del sidebar mini usan `position: fixed` con `left: 82px` para escapar el contexto de clipping del sidebar
- La búsqueda global (`/search`) devuelve JSON y es consumida por `SearchModal.svelte` con debounce de 220ms

---

*SPEC v1.0 — Febrero 2026*
