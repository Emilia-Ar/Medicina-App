<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

// 🔔 Web Push (paquete Notification Channels)
use NotificationChannels\WebPush\HasPushSubscriptions;
use NotificationChannels\WebPush\PushSubscription;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasPushSubscriptions; // ← añadimos HasPushSubscriptions

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relaciones
    public function medications(): HasMany
    {
        return $this->hasMany(Medication::class);
    }

    public function takes(): HasMany
    {
        return $this->hasMany(Take::class);
        // Nota: si en tu proyecto el modelo se llama "Intake", cambia a:
        // return $this->hasMany(Intake::class);
    }

    // 🔔 Suscripciones Web Push
    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
        // Si el trait ya define este método en tu versión del paquete,
        // podés eliminarlo para evitar duplicación.
    }
}

