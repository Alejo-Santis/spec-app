<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientPrice extends Model
{
    protected $fillable = [
        'client_id',
        'service_type_id',
        'price_list_id',
        'duration_years',
        'base_price',
        'adjustment_percentage',
        'negotiated_price',
        'discount_percentage',
        'final_price',
        'applies_iva',
        'iva_percentage',
        'valid_from',
        'valid_until',
        'notes',
    ];

    protected $casts = [
        'duration_years'        => 'integer',
        'base_price'            => 'decimal:2',
        'adjustment_percentage' => 'decimal:2',
        'negotiated_price'      => 'decimal:2',
        'discount_percentage'   => 'decimal:2',
        'final_price'           => 'decimal:2',
        'applies_iva'           => 'boolean',
        'iva_percentage'        => 'decimal:2',
        'valid_from'            => 'date',
        'valid_until'           => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(PriceAdjustment::class);
    }

    public function getFinalPriceWithIvaAttribute(): float
    {
        if (! $this->applies_iva) {
            return (float) $this->final_price;
        }

        return round((float) $this->final_price * (1 + (float) $this->iva_percentage / 100), 2);
    }
}
