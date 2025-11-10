<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    /**
     * Guarda una nueva suscripción push del usuario.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string|max:500',
            'publicKey' => 'nullable|string',
            'authToken' => 'nullable|string',
        ]);

        // Usamos updateOrCreate para evitar duplicados
        auth()->user()->pushSubscriptions()->updateOrCreate(
            [
                'endpoint' => $validated['endpoint'],
            ],
            [
                'public_key' => $validated['publicKey'],
                'auth_token' => $validated['authToken'],
            ]
        );

        return response()->json(['success' => true], 201);
    }

    /**
     * (Opcional pero recomendado) Elimina una suscripción.
     */
    public function destroy(Request $request)
    {
        $request->validate(['endpoint' => 'required|string']);

        auth()->user()->pushSubscriptions()
            ->where('endpoint', $request->endpoint)
            ->delete();

        return response()->json(['success' => true]);
    }
}
