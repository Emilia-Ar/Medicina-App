<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Take extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'medication_id',
        'scheduled_at',
        'completed_at',
        'notified_at',
    ];

    // Aseguramos que Eloquent trate estos campos como fechas
    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'notified_at'  => 'datetime',
    ];

    // Relación: Una toma pertenece a un usuario
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relación: Una toma pertenece a un medicamento
    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
}