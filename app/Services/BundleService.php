<?php

namespace App\Services;

use App\Models\BundleConsumption;
use App\Models\ClientBundle;
use Illuminate\Support\Facades\DB;

class BundleService
{
    public function consume(
        ClientBundle $bundle,
        int $quantity,
        string $description = '',
        string $reference = '',
    ): BundleConsumption {
        if ($bundle->quantity_remaining < $quantity) {
            throw new \RuntimeException(
                "Saldo insuficiente en la bolsa. Disponible: {$bundle->quantity_remaining}"
            );
        }

        return DB::transaction(function () use ($bundle, $quantity, $description, $reference) {
            $consumption = $bundle->consumptions()->create([
                'quantity'    => $quantity,
                'consumed_at' => now(),
                'description' => $description,
                'reference'   => $reference,
                'created_by'  => auth()->id(),
            ]);

            $bundle->increment('quantity_consumed', $quantity);
            $bundle->refresh();

            if ($bundle->quantity_remaining <= 0) {
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
