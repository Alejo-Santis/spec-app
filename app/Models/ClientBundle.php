<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientBundle extends Model
{
    use HasUuid;

    protected $fillable = [
        'client_id',
        'bundle_tier_id',
        'price_list_id',
        'quantity_purchased',
        'quantity_consumed',
        'price_paid',
        'purchased_at',
        'expires_at',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'quantity_purchased' => 'integer',
        'quantity_consumed'  => 'integer',
        'price_paid'         => 'decimal:2',
        'purchased_at'       => 'date',
        'expires_at'         => 'date',
        'is_active'          => 'boolean',
    ];

    protected $appends = ['quantity_remaining'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function bundleTier(): BelongsTo
    {
        return $this->belongsTo(BundleTier::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function consumptions(): HasMany
    {
        return $this->hasMany(BundleConsumption::class);
    }

    public function getQuantityRemainingAttribute(): int
    {
        return $this->quantity_purchased - $this->quantity_consumed;
    }

    public function getConsumptionPercentageAttribute(): float
    {
        if ($this->quantity_purchased === 0) {
            return 0;
        }

        return round(($this->quantity_consumed / $this->quantity_purchased) * 100, 1);
    }
}
