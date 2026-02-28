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
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServiceTypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
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
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])
        ->middleware('can:activity-logs.view')
        ->name('activity-logs.index');

    // Búsqueda global (JSON)
    Route::get('/search', SearchController::class)->name('search');

    // Import / Export
    Route::middleware('can:import-export.use')->group(function () {
        Route::get('/clients/export',         [ImportExportController::class, 'exportClients'])->name('clients.export');
        Route::post('/clients/import',        [ImportExportController::class, 'importClients'])->name('clients.import');
        Route::get('/clients/template',       [ImportExportController::class, 'templateClients'])->name('clients.template');
        Route::get('/client-prices/export',   [ImportExportController::class, 'exportClientPrices'])->name('client-prices.export');
        Route::post('/client-prices/import',  [ImportExportController::class, 'importClientPrices'])->name('client-prices.import');
        Route::get('/client-prices/template', [ImportExportController::class, 'templateClientPrices'])->name('client-prices.template');
        Route::get('/client-bundles/export',  [ImportExportController::class, 'exportClientBundles'])->name('client-bundles.export');
    });

    // Cotización PDF
    Route::get('/clients/{client}/quotation', [QuotationController::class, 'download'])
        ->middleware('can:clients.view')
        ->name('clients.quotation');

    // Clientes
    Route::get('clients', [ClientController::class, 'index'])
        ->middleware('can:clients.view')->name('clients.index');
    Route::get('clients/create', [ClientController::class, 'create'])
        ->middleware('can:clients.create')->name('clients.create');
    Route::post('clients', [ClientController::class, 'store'])
        ->middleware('can:clients.create')->name('clients.store');
    Route::get('clients/{client}', [ClientController::class, 'show'])
        ->middleware('can:clients.view')->name('clients.show');
    Route::get('clients/{client}/edit', [ClientController::class, 'edit'])
        ->middleware('can:clients.update')->name('clients.edit');
    Route::put('clients/{client}', [ClientController::class, 'update'])
        ->middleware('can:clients.update')->name('clients.update');
    Route::delete('clients/{client}', [ClientController::class, 'destroy'])
        ->middleware('can:clients.delete')->name('clients.destroy');

    Route::get('clients/{client}/prices', [ClientPriceController::class, 'clientPrices'])
        ->middleware('can:client-prices.view')->name('clients.prices');
    Route::get('clients/{client}/bundles', [ClientBundleController::class, 'clientBundles'])
        ->middleware('can:client-bundles.view')->name('clients.bundles');

    // Listas de precios
    Route::get('price-lists', [PriceListController::class, 'index'])
        ->middleware('can:price-lists.view')->name('price-lists.index');
    Route::get('price-lists/create', [PriceListController::class, 'create'])
        ->middleware('can:price-lists.create')->name('price-lists.create');
    Route::post('price-lists', [PriceListController::class, 'store'])
        ->middleware('can:price-lists.create')->name('price-lists.store');
    Route::get('price-lists/{priceList}', [PriceListController::class, 'show'])
        ->middleware('can:price-lists.view')->name('price-lists.show');
    Route::post('price-lists/{priceList}/generate-from-previous', [PriceListController::class, 'generateFromPrevious'])
        ->middleware('can:price-lists.generate')->name('price-lists.generate');
    Route::post('price-lists/{priceList}/activate', [PriceListController::class, 'activate'])
        ->middleware('can:price-lists.activate')->name('price-lists.activate');

    // Tipos de servicio
    Route::get('service-types', [ServiceTypeController::class, 'index'])
        ->middleware('can:service-types.view')->name('service-types.index');
    Route::post('service-types', [ServiceTypeController::class, 'store'])
        ->middleware('can:service-types.manage')->name('service-types.store');
    Route::put('service-types/{serviceType}', [ServiceTypeController::class, 'update'])
        ->middleware('can:service-types.manage')->name('service-types.update');
    Route::delete('service-types/{serviceType}', [ServiceTypeController::class, 'destroy'])
        ->middleware('can:service-types.manage')->name('service-types.destroy');

    // Bundle tiers
    Route::get('price-lists/{priceList}/bundle-tiers/create', [BundleTierController::class, 'create'])
        ->middleware('can:bundle-tiers.manage')->name('price-lists.bundle-tiers.create');
    Route::post('price-lists/{priceList}/bundle-tiers', [BundleTierController::class, 'store'])
        ->middleware('can:bundle-tiers.manage')->name('price-lists.bundle-tiers.store');
    Route::get('bundle-tiers/{bundleTier}/edit', [BundleTierController::class, 'edit'])
        ->middleware('can:bundle-tiers.manage')->name('bundle-tiers.edit');
    Route::put('bundle-tiers/{bundleTier}', [BundleTierController::class, 'update'])
        ->middleware('can:bundle-tiers.manage')->name('bundle-tiers.update');
    Route::delete('bundle-tiers/{bundleTier}', [BundleTierController::class, 'destroy'])
        ->middleware('can:bundle-tiers.manage')->name('bundle-tiers.destroy');

    // Precios por cliente
    Route::get('client-prices', [ClientPriceController::class, 'index'])
        ->middleware('can:client-prices.view')->name('client-prices.index');
    Route::get('client-prices/create', [ClientPriceController::class, 'create'])
        ->middleware('can:client-prices.create')->name('client-prices.create');
    Route::post('client-prices', [ClientPriceController::class, 'store'])
        ->middleware('can:client-prices.create')->name('client-prices.store');
    Route::get('client-prices/{clientPrice}/edit', [ClientPriceController::class, 'edit'])
        ->middleware('can:client-prices.update')->name('client-prices.edit');
    Route::put('client-prices/{clientPrice}', [ClientPriceController::class, 'update'])
        ->middleware('can:client-prices.update')->name('client-prices.update');
    Route::delete('client-prices/{clientPrice}', [ClientPriceController::class, 'destroy'])
        ->middleware('can:client-prices.delete')->name('client-prices.destroy');

    // Bolsas
    Route::get('client-bundles', [ClientBundleController::class, 'index'])
        ->middleware('can:client-bundles.view')->name('client-bundles.index');
    Route::get('client-bundles/create', [ClientBundleController::class, 'create'])
        ->middleware('can:client-bundles.create')->name('client-bundles.create');
    Route::post('client-bundles', [ClientBundleController::class, 'store'])
        ->middleware('can:client-bundles.create')->name('client-bundles.store');
    Route::get('client-bundles/{clientBundle}', [ClientBundleController::class, 'show'])
        ->middleware('can:client-bundles.view')->name('client-bundles.show');
    Route::get('client-bundles/{clientBundle}/edit', [ClientBundleController::class, 'edit'])
        ->middleware('can:client-bundles.update')->name('client-bundles.edit');
    Route::put('client-bundles/{clientBundle}', [ClientBundleController::class, 'update'])
        ->middleware('can:client-bundles.update')->name('client-bundles.update');

    Route::post('client-bundles/{clientBundle}/consume', [BundleConsumptionController::class, 'store'])
        ->middleware('can:client-bundles.consume')->name('client-bundles.consume');
    Route::get('client-bundles/{clientBundle}/consumptions', [BundleConsumptionController::class, 'index'])
        ->middleware('can:client-bundles.view')->name('client-bundles.consumptions');

    // Gestión de usuarios (solo admin)
    Route::get('users', [UserController::class, 'index'])
        ->middleware('can:users.manage')->name('users.index');
    Route::post('users', [UserController::class, 'store'])
        ->middleware('can:users.manage')->name('users.store');
    Route::put('users/{user}/role', [UserController::class, 'updateRole'])
        ->middleware('can:users.manage')->name('users.update-role');
    Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])
        ->middleware('can:users.manage')->name('users.toggle-active');
    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->middleware('can:users.manage')->name('users.destroy');

    // Perfil propio (cualquier usuario autenticado)
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile/info', [ProfileController::class, 'updateInfo'])->name('profile.update-info');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
});
