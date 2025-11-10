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

        // Buscamos tomas que:
        // 1. No estén completadas (completed_at IS NULL)
        // 2. No hayan sido notificadas (notified_at IS NULL)
        // 3. Estén en el rango de tiempo (ej: 5 minutos antes o hasta 1 minuto después de la hora)
        $takes = Take::whereNull('completed_at')
            ->whereNull('notified_at')
            ->where('scheduled_at', '<=', $now) // Programadas para ahora o el pasado
            ->where('scheduled_at', '>=', $now->copy()->subMinutes(5)) // No más de 5 min en el pasado
            ->with(['user', 'medication'])
            ->get();

        if ($takes->isEmpty()) {
            $this->info('No hay recordatorios pendientes.');
            return;
        }

        $this->info("Enviando {$takes->count()} recordatorios...");

        foreach ($takes as $take) {
            // Enviamos la notificación al usuario dueño de la toma
            $take->user->notify(new TakeReminder($take));
            
            // ¡Marcamos la toma como notificada para no volver a enviarla!
            $take->update(['notified_at' => now()]);
        }
        
        $this->info('Recordatorios enviados.');
    }
}