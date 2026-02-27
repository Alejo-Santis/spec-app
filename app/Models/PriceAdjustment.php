<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceAdjustment extends Model
{
    protected $fillable = [
        'client_price_id',
        'old_price',
        'new_price',
        'reason',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
    ];

    public function clientPrice(): BelongsTo
    {
        return $this->belongsTo(ClientPrice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
