<?php

namespace App\Http\Controllers;

use App\Models\Take;
use Illuminate\Http\Request;

class TakeController extends Controller
{
    /**
     * Marca una toma como completada (o la salta).
     */
    public function complete(Request $request, Take $take)
    {
        // Seguridad:
        if ($take->user_id !== auth()->id()) {
            abort(403);
        }

        // Si ya está completada, no hacemos nada
        if ($take->completed_at) {
            return back()->with('info', 'Esa toma ya estaba registrada.');
        }

        // Marcamos como completada
        $take->update([
            'completed_at' => now()
        ]);

        // Descontamos del stock
        $medication = $take->medication;
        $contableTypes = ['unit', 'half', 'quarter'];

        if (in_array($medication->dose_type, $contableTypes)) {

            // Descontamos del stock
            $medication->update([
                'current_stock' => $medication->current_stock - $medication->dose_quantity
            ]);

            // TODO: Enviar alerta si 'current_stock' está bajo
        }
        // ▲▲▲ ¡FIN DE LA LÓGICA! ▲▲▲

        return back()->with('status', '¡Toma registrada! Buen trabajo.');
    }
}