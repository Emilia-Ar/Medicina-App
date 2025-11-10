<?php

namespace App\Services;

use App\Models\Medication;
use App\Models\Take;
use Carbon\Carbon;

class ScheduleService
{
    /**
     * Genera las tomas para un medicamento para los próximos X días.
     */
    public function generateTakes(Medication $medication, int $daysToGenerate = 30)
    {
        $now = Carbon::now();
        
        // Obtenemos la última toma programada (completada o no)
        $lastTake = $medication->takes()->orderBy('scheduled_at', 'desc')->first();
        
        // $currentScheduleTime; // <-- ¡ESTA LÍNEA (21) HA SIDO ELIMINADA!

        if ($lastTake) {
            // Lógica para un medicamento existente (Editado):
            // Empezamos a calcular desde la siguiente toma programada
            $currentScheduleTime = $lastTake->scheduled_at->copy()->addHours($medication->frequency_hours);
        
        } else {
            // Lógica para un medicamento NUEVO:
            // Debemos encontrar la *próxima* hora de inicio válida.
            
            // 1. Tomamos la hora de inicio configurada para HOY
            $currentScheduleTime = $now->copy()->setTimeFromTimeString($medication->start_time);
            
            // 2. ¡LÓGICA CORREGIDA!
            // Si esa hora ya pasó (ej: 8 AM y son las 5 PM)...
            while ($currentScheduleTime->lessThan($now)) {
                
                // ...calculamos la siguiente toma (ej: 8 AM + 8h = 4 PM).
                // Si 4 PM también pasó (porque son las 5 PM)...
                // ...calculamos la siguiente (ej: 4 PM + 8h = 12 AM mañana).
                // Esta (12 AM) será la primera toma que generaremos.
                $currentScheduleTime->addHours($medication->frequency_hours);
            }
            // 3. $currentScheduleTime ahora es la primera toma futura válida.
        }
        
        $endDate = $now->copy()->addDays($daysToGenerate);
        $takesToInsert = [];

        // Bucle para generar las tomas futuras
        while ($currentScheduleTime->lessThanOrEqualTo($endDate)) {
            $takesToInsert[] = [
                'user_id' => $medication->user_id,
                'medication_id' => $medication->id,
                'scheduled_at' => $currentScheduleTime->toDateTimeString(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
            
            // Avanzamos a la siguiente toma
            $currentScheduleTime->addHours($medication->frequency_hours);
        }

        // Insertamos todas las tomas de golpe
        // Usamos firstOrCreate para evitar duplicados si se re-ejecuta
        foreach ($takesToInsert as $takeData) {
            Take::firstOrCreate(
                [
                    'user_id' => $takeData['user_id'],
                    'medication_id' => $takeData['medication_id'],
                    'scheduled_at' => $takeData['scheduled_at'],
                ],
                $takeData
            );
        }
    }
}