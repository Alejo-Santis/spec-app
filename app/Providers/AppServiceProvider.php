<?php

namespace App\Providers;

use App\Models\ClientBundle;
use App\Models\ClientPrice;
use App\Observers\ClientBundleObserver;
use App\Observers\ClientPriceObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        ClientPrice::observe(ClientPriceObserver::class);
        ClientBundle::observe(ClientBundleObserver::class);
    }
}
