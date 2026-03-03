<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyUsage extends Model
{
    use HasUuid;

    protected $fillable = [
        'client_id',
        'client_price_id',
        'period_year',
        'period_month',
        'document_count',
        'unit_price',
        'total_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'period_year'    => 'integer',
        'period_month'   => 'integer',
        'document_count' => 'integer',
        'unit_price'     => 'decimal:4',
        'total_amount'   => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function clientPrice(): BelongsTo
    {
        return $this->belongsTo(ClientPrice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Nombre legible del período: "Marzo 2026"
    public function getPeriodLabelAttribute(): string
    {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        return ($months[$this->period_month] ?? $this->period_month) . ' ' . $this->period_year;
    }
}
