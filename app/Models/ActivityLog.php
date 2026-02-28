<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'module',
        'subject_id',
        'subject_label',
        'description',
        'properties',
        'ip_address',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Etiquetas legibles para cada acción
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'created'   => 'Creado',
            'updated'   => 'Actualizado',
            'deleted'   => 'Eliminado',
            'activated' => 'Activado',
            'consumed'  => 'Consumo registrado',
            'imported'  => 'Importado',
            'exported'  => 'Exportado',
            default     => ucfirst($this->action),
        };
    }

    // Color badge por acción
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'created'   => 'success',
            'updated'   => 'info',
            'deleted'   => 'danger',
            'activated' => 'primary',
            'consumed'  => 'warning',
            'imported'  => 'secondary',
            'exported'  => 'secondary',
            default     => 'light',
        };
    }
}
