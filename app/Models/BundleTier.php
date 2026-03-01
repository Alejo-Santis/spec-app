<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BundleTier extends Model
{
    use HasUuid;
    protected $fillable = [
        'service_type_id',
        'price_list_id',
        'name',
        'quantity',
        'price',
        'is_active',
    ];

    protected $casts = [
        'quantity'   => 'integer',
        'price'      => 'decimal:2',
        'unit_price' => 'decimal:4',
        'is_active'  => 'boolean',
    ];

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function clientBundles(): HasMany
    {
        return $this->hasMany(ClientBundle::class);
    }
}
