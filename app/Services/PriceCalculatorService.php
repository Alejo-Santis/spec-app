<?php

namespace App\Services;

class PriceCalculatorService
{
    public function calculate(
        float $basePrice,
        float $adjustmentPercentage,
        ?float $negotiatedPrice = null,
        ?float $discountPercentage = null,
    ): float {
        if ($negotiatedPrice !== null) {
            $price = $negotiatedPrice;
        } else {
            $price = $basePrice * (1 + $adjustmentPercentage / 100);
        }

        if ($discountPercentage !== null) {
            $price = $price * (1 - $discountPercentage / 100);
        }

        return round($price, 2);
    }

    public function withIva(float $price, float $ivaPercentage = 19.00): float
    {
        return round($price * (1 + $ivaPercentage / 100), 2);
    }

    public function formatCop(float $amount): string
    {
        return '$ ' . number_format($amount, 0, ',', '.');
    }
}
