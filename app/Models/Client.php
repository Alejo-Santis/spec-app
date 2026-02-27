<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'business_name',
        'trade_name',
        'document_number',
        'dv',
        'tax_regime',
        'tax_responsibilities',
        'ciiu_code',
        'email',
        'email_billing',
        'phone',
        'mobile',
        'address',
        'city',
        'department',
        'country',
        'postal_code',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'tax_responsibilities' => 'array',
        'is_active'            => 'boolean',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(ClientPrice::class);
    }

    public function bundles(): HasMany
    {
        return $this->hasMany(ClientBundle::class);
    }

    public function activeBundles(): HasMany
    {
        return $this->bundles()->where('is_active', true);
    }

    public function currentPrices(): HasMany
    {
        return $this->prices()->whereHas('priceList', fn ($q) => $q->where('is_active', true));
    }

    public function getFormattedDocumentAttribute(): string
    {
        if ($this->type === 'juridica' && $this->dv) {
            return number_format((float) $this->document_number, 0, ',', '.') . '-' . $this->dv;
        }

        return $this->document_number;
    }
}
