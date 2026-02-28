<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Revisar saldo de bolsas cada día a las 8:00 AM y enviar alertas
Schedule::command('bundles:check-balances')->dailyAt('08:00');
