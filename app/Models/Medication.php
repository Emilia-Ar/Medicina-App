<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medication extends Model
{
    use HasFactory;

    // Propiedades que se pueden asignar masivamente
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'photo_path',
        'total_stock',
        'stock_unit',
        'current_stock',
        'dose_quantity',
        'dose_type',
        'frequency_hours',
        'start_time',
    ];

    protected $casts = [
        'frequency_hours' => 'integer',
        'total_stock'     => 'integer',
        'current_stock'   => 'integer',
        'dose_quantity'   => 'integer',
    ];

    // Relación: Un medicamento pertenece a un usuario
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relación: Un medicamento tiene muchas tomas programadas
    public function takes(): HasMany
    {
        return $this->hasMany(Take::class);
    }
}