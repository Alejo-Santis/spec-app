<?php

namespace App\Services;

use App\Models\BundleTier;
use App\Models\ClientPrice;
use App\Models\PriceAdjustment;
use App\Models\PriceList;
use Illuminate\Support\Facades\DB;

class PriceListGeneratorService
{
    public function __construct(private PriceCalculatorService $calculator) {}

    public function generateFromPrevious(PriceList $newList, PriceList $previousList): void
    {
        DB::transaction(function () use ($newList, $previousList) {
            $this->copyBundleTiers($newList, $previousList);
            $this->copyClientPrices($newList, $previousList);
        });
    }

    private function copyBundleTiers(PriceList $newList, PriceList $previousList): void
    {
        foreach ($previousList->bundleTiers as $tier) {
            $newPrice = round((float) $tier->price * (1 + (float) $newList->adjustment_percentage / 100), 2);

            BundleTier::create([
                'service_type_id' => $tier->service_type_id,
                'price_list_id'   => $newList->id,
                'name'            => $tier->name,
                'quantity'        => $tier->quantity,
                'price'           => $newPrice,
                'is_active'       => $tier->is_active,
            ]);
        }
    }

    private function copyClientPrices(PriceList $newList, PriceList $previousList): void
    {
        foreach ($previousList->clientPrices as $oldPrice) {
            $newFinalPrice = $this->calculator->calculate(
                basePrice: (float) $oldPrice->final_price,
                adjustmentPercentage: (float) $newList->adjustment_percentage,
            );

            $newClientPrice = ClientPrice::create([
                'client_id'             => $oldPrice->client_id,
                'service_type_id'       => $oldPrice->service_type_id,
                'price_list_id'         => $newList->id,
                'duration_years'        => $oldPrice->duration_years,
                'base_price'            => $oldPrice->final_price,
                'adjustment_percentage' => $newList->adjustment_percentage,
                'negotiated_price'      => null,
                'discount_percentage'   => null,
                'final_price'           => $newFinalPrice,
                'applies_iva'           => $oldPrice->applies_iva,
                'iva_percentage'        => $oldPrice->iva_percentage,
                'valid_from'            => now()->startOfYear(),
                'valid_until'           => null,
                'notes'                 => null,
            ]);

            PriceAdjustment::create([
                'client_price_id' => $newClientPrice->id,
                'old_price'       => $oldPrice->final_price,
                'new_price'       => $newFinalPrice,
                'reason'          => 'annual_adjust',
                'created_by'      => auth()->id() ?? 1,
            ]);
        }
    }
}
