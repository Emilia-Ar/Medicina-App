<?php

namespace App\Console\Commands;

use App\Models\Take;
use App\Notifications\TakeReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification; // <-- Importar

class SendTakeReminders extends Command
{
    /**
     * La firma del comando.
     */
    protected $signature = 'app:send-take-reminders';

    /**
     * La descripción del comando.
     */
    protected $description = 'Envía recordatorios de tomas de medicamentos pendientes.';

    /**
     * Ejecuta el comando.
     */
    public function handle()
{
    $this->info('Buscando recordatorios para enviar...');

    $now = now();

    // Para probar: todas las tomas pendientes y no notificadas
    $takes = Take::whereNull('completed_at')
        ->whereNull('notified_at')
        ->where('scheduled_at', '<=', $now)
        ->with(['user', 'medication'])
        ->get();

    if ($takes->isEmpty()) {
        $this->info('No hay recordatorios pendientes.');
        return;
    }

    $this->info("Encontradas {$takes->count()} tomas para recordar.");

    foreach ($takes as $take) {
        $user = $take->user;

        if (! $user) {
            $this->warn("Toma {$take->id} sin usuario asociado. Se salta.");
            continue;
        }

        // ¿Tiene suscripciones webpush?
        $subsCount = $user->pushSubscriptions()->count();

        if ($subsCount === 0) {
            $this->warn("Usuario {$user->id} sin suscripciones WebPush. Se salta.");
            continue;
        }

        $this->info(" → Enviando recordatorio de {$take->medication->name} al usuario {$user->id} ({$subsCount} subs).");

        $user->notify(new TakeReminder($take));

        // Marcamos como notificada
        $take->update(['notified_at' => now()]);
    }

    $this->info('Recordatorios procesados.');
}
}