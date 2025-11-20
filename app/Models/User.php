<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

// 🔔 Web Push (paquete Notification Channels)
use NotificationChannels\WebPush\HasPushSubscriptions;
use NotificationChannels\WebPush\PushSubscription as WebPushSubscription;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasPushSubscriptions; // ← importante

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
            'password'          => 'hashed',
        ];
    }

    // ─────────────────── Relaciones propias de tu app ───────────────────

    public function medications(): HasMany
    {
        return $this->hasMany(Medication::class);
    }

    public function takes(): HasMany
    {
        return $this->hasMany(Take::class);
    }

    // ─────────────────── WebPush: relación y ruta de notificación ───────────────────

    /**
     * Relación con las suscripciones WebPush.
     * Usamos el modelo del paquete: NotificationChannels\WebPush\PushSubscription
     */
    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(WebPushSubscription::class);
    }

    /**
     * Laravel usa este método para saber a qué suscripciones enviar.
     * SIEMPRE devolvemos una Collection (aunque esté vacía), nunca null.
     */
    public function routeNotificationForWebPush($notification = null)
    {
        $subs = $this->pushSubscriptions()->get();

        \Log::info('routeNotificationForWebPush', [
            'user_id' => $this->id,
            'count'   => $subs->count(),
        ]);

        return $subs;
    }
}


