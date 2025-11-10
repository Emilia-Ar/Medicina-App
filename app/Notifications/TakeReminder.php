<?php

namespace App\Notifications;

use App\Models\Take;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TakeReminder extends Notification
{
    use Queueable;

    protected $take;

    /**
     * Crea una nueva instancia de la notificación.
     */
    public function __construct(Take $take)
    {
        $this->take = $take;
    }

    /**
     * Define los canales de entrega.
     */
    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    /**
     * Construye el mensaje de Web Push.
     */
    public function toWebPush($notifiable, $notification)
    {
        $medication = $this->take->medication;
        $title = "¡Hora de tu medicina!";
        
        // Mensaje actualizado
        $body = "No olvides tu dosis de {$medication->name}.";
        
        // URL pública de la foto (si existe)
        $imageUrl = $medication->photo_path 
            ? Storage::url($medication->photo_path) 
            : null;

        return (new WebPushMessage)
            ->title($title)
            ->body($body)
            ->icon('/images/icons/icon-192x192.png') // Icono por defecto
            ->image($imageUrl) // Foto del medicamento
            ->data([
                // Pasamos la URL a la que debe ir el clic
                'url' => route('medications.show', $medication), 
            ])
            ->requireInteraction(true) // Mantiene la notificación en pantalla
            
            // Botones de acción actualizados
            ->action('No olvides tu dosis', 'open_app') // Botón 1 (Abre la app)
            ->action('Saltar', 'skip');               // Botón 2 (Cierra la alerta)
    }
}
