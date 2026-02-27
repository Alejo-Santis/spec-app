<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceList extends Model
{
    protected $fillable = [
        'year',
        'name',
        'adjustment_percentage',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'year'                  => 'integer',
        'adjustment_percentage' => 'decimal:2',
        'is_active'             => 'boolean',
    ];

    public function bundleTiers(): HasMany
    {
        return $this->hasMany(BundleTier::class);
    }

    public function clientPrices(): HasMany
    {
        return $this->hasMany(ClientPrice::class);
    }

    public function clientBundles(): HasMany
    {
        return $this->hasMany(ClientBundle::class);
    }
}
