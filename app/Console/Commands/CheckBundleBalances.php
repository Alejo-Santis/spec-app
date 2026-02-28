<?php

namespace App\Console\Commands;

use App\Models\ClientBundle;
use App\Models\User;
use App\Notifications\BundleLowBalanceNotification;
use Illuminate\Console\Command;

class CheckBundleBalances extends Command
{
    protected $signature   = 'bundles:check-balances';
    protected $description = 'Envía alertas por email cuando una bolsa tiene menos del 10% de saldo';

    public function handle(): int
    {
        $criticalBundles = ClientBundle::with(['client', 'bundleTier'])
            ->where('is_active', true)
            ->get()
            ->filter(fn ($bundle) => ($bundle->quantity_purchased - $bundle->quantity_consumed) / $bundle->quantity_purchased <= 0.10);

        if ($criticalBundles->isEmpty()) {
            $this->info('No hay bolsas en estado crítico.');
            return self::SUCCESS;
        }

        // Notifica a todos los usuarios con rol admin u operator
        $admins = User::role(['admin', 'operator'])->get();

        foreach ($criticalBundles as $bundle) {
            foreach ($admins as $admin) {
                $admin->notify(new BundleLowBalanceNotification($bundle));
            }
            $this->line(" <comment>⚠</comment>  {$bundle->client->business_name} — {$bundle->bundleTier->name}");
        }

        $this->info("Alertas enviadas para {$criticalBundles->count()} bolsa(s).");

        return self::SUCCESS;
    }
}
