<?php

namespace App\Observers;

use App\Models\ClientPrice;
use App\Models\PriceAdjustment;
use App\Services\PriceCalculatorService;

class ClientPriceObserver
{
    public function __construct(private PriceCalculatorService $calculator) {}

    public function saving(ClientPrice $clientPrice): void
    {
        $clientPrice->final_price = $this->calculator->calculate(
            basePrice: (float) $clientPrice->base_price,
            adjustmentPercentage: (float) ($clientPrice->adjustment_percentage
                ?? $clientPrice->priceList?->adjustment_percentage
                ?? 0),
            negotiatedPrice: $clientPrice->negotiated_price !== null
                ? (float) $clientPrice->negotiated_price
                : null,
            discountPercentage: $clientPrice->discount_percentage !== null
                ? (float) $clientPrice->discount_percentage
                : null,
        );
    }

    public function updated(ClientPrice $clientPrice): void
    {
        if (! $clientPrice->wasChanged('final_price')) {
            return;
        }

        $oldPrice = $clientPrice->getOriginal('final_price');

        if ($oldPrice === null || (float) $oldPrice === (float) $clientPrice->final_price) {
            return;
        }

        $reason = $clientPrice->wasChanged('negotiated_price') ? 'negotiation' : 'annual_adjust';

        PriceAdjustment::create([
            'client_price_id' => $clientPrice->id,
            'old_price'       => $oldPrice,
            'new_price'       => $clientPrice->final_price,
            'reason'          => $reason,
            'created_by'      => auth()->id() ?? 1,
        ]);
    }
}
