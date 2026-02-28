<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BundleConsumptionController;
use App\Http\Controllers\BundleTierController;
use App\Http\Controllers\ClientBundleController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientPriceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\PriceListController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServiceTypeController;
use Illuminate\Support\Facades\Route;

// Auth
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// App
Route::middleware(['auth'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Log de actividades
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    // Búsqueda global (JSON)
    Route::get('/search', SearchController::class)->name('search');

    // Import / Export
    Route::get('/clients/export',          [ImportExportController::class, 'exportClients'])->name('clients.export');
    Route::post('/clients/import',         [ImportExportController::class, 'importClients'])->name('clients.import');
    Route::get('/clients/template',        [ImportExportController::class, 'templateClients'])->name('clients.template');
    Route::get('/client-prices/export',    [ImportExportController::class, 'exportClientPrices'])->name('client-prices.export');
    Route::post('/client-prices/import',   [ImportExportController::class, 'importClientPrices'])->name('client-prices.import');
    Route::get('/client-prices/template',  [ImportExportController::class, 'templateClientPrices'])->name('client-prices.template');
    Route::get('/client-bundles/export',   [ImportExportController::class, 'exportClientBundles'])->name('client-bundles.export');

    // Clientes
    Route::resource('clients', ClientController::class);
    Route::get('clients/{client}/prices', [ClientPriceController::class, 'clientPrices'])
        ->name('clients.prices');
    Route::get('clients/{client}/bundles', [ClientBundleController::class, 'clientBundles'])
        ->name('clients.bundles');

    // Listas de precios
    Route::resource('price-lists', PriceListController::class)->except(['edit', 'update', 'destroy']);
    Route::post('price-lists/{priceList}/generate-from-previous', [PriceListController::class, 'generateFromPrevious'])
        ->name('price-lists.generate');
    Route::post('price-lists/{priceList}/activate', [PriceListController::class, 'activate'])
        ->name('price-lists.activate');

    // Tipos de servicio
    Route::resource('service-types', ServiceTypeController::class)->except(['show']);

    // Bundle tiers (shallow: create/store en priceList, edit/update/destroy en bundleTier)
    Route::get('price-lists/{priceList}/bundle-tiers/create', [BundleTierController::class, 'create'])
        ->name('price-lists.bundle-tiers.create');
    Route::post('price-lists/{priceList}/bundle-tiers', [BundleTierController::class, 'store'])
        ->name('price-lists.bundle-tiers.store');
    Route::get('bundle-tiers/{bundleTier}/edit', [BundleTierController::class, 'edit'])
        ->name('bundle-tiers.edit');
    Route::put('bundle-tiers/{bundleTier}', [BundleTierController::class, 'update'])
        ->name('bundle-tiers.update');
    Route::delete('bundle-tiers/{bundleTier}', [BundleTierController::class, 'destroy'])
        ->name('bundle-tiers.destroy');

    // Precios por cliente
    Route::get('client-prices', [ClientPriceController::class, 'index'])->name('client-prices.index');
    Route::get('client-prices/create', [ClientPriceController::class, 'create'])->name('client-prices.create');
    Route::post('client-prices', [ClientPriceController::class, 'store'])->name('client-prices.store');
    Route::get('client-prices/{clientPrice}/edit', [ClientPriceController::class, 'edit'])->name('client-prices.edit');
    Route::put('client-prices/{clientPrice}', [ClientPriceController::class, 'update'])->name('client-prices.update');
    Route::delete('client-prices/{clientPrice}', [ClientPriceController::class, 'destroy'])->name('client-prices.destroy');

    // Bolsas
    Route::get('client-bundles', [ClientBundleController::class, 'index'])->name('client-bundles.index');
    Route::get('client-bundles/create', [ClientBundleController::class, 'create'])->name('client-bundles.create');
    Route::post('client-bundles', [ClientBundleController::class, 'store'])->name('client-bundles.store');
    Route::get('client-bundles/{clientBundle}', [ClientBundleController::class, 'show'])->name('client-bundles.show');
    Route::get('client-bundles/{clientBundle}/edit', [ClientBundleController::class, 'edit'])->name('client-bundles.edit');
    Route::put('client-bundles/{clientBundle}', [ClientBundleController::class, 'update'])->name('client-bundles.update');

    Route::post('client-bundles/{clientBundle}/consume', [BundleConsumptionController::class, 'store'])
        ->name('client-bundles.consume');
    Route::get('client-bundles/{clientBundle}/consumptions', [BundleConsumptionController::class, 'index'])
        ->name('client-bundles.consumptions');
});
