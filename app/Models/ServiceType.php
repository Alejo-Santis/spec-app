<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceType extends Model
{
    protected $fillable = [
        'name',
        'billing_type',
        'applies_iva',
        'iva_percentage',
        'description',
        'is_active',
    ];

    protected $casts = [
        'applies_iva'    => 'boolean',
        'iva_percentage' => 'decimal:2',
        'is_active'      => 'boolean',
    ];

    public function bundleTiers(): HasMany
    {
        return $this->hasMany(BundleTier::class);
    }

    public function clientPrices(): HasMany
    {
        return $this->hasMany(ClientPrice::class);
    }

    public function isBundle(): bool
    {
        return $this->billing_type === 'bundle';
    }
}
