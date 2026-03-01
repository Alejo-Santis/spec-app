<?php

namespace App\Providers;

use App\Listeners\LogAuthActivity;
use App\Models\ClientBundle;
use App\Models\ClientPrice;
use App\Observers\ClientBundleObserver;
use App\Observers\ClientPriceObserver;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        ClientPrice::observe(ClientPriceObserver::class);
        ClientBundle::observe(ClientBundleObserver::class);

        Event::listen(Login::class, [LogAuthActivity::class, 'handleLogin']);
        Event::listen(Logout::class, [LogAuthActivity::class, 'handleLogout']);
        Event::listen(Failed::class, [LogAuthActivity::class, 'handleFailed']);
    }
}
