<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BundleConsumption extends Model
{
    protected $fillable = [
        'client_bundle_id',
        'quantity',
        'consumed_at',
        'description',
        'reference',
        'created_by',
    ];

    protected $casts = [
        'quantity'    => 'integer',
        'consumed_at' => 'datetime',
    ];

    public function clientBundle(): BelongsTo
    {
        return $this->belongsTo(ClientBundle::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
