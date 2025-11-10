<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TakeController; // <-- ¡Añade esta importación!

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí es donde puedes registrar rutas de API para tu aplicación. Estas
| rutas se cargan automáticamente por RouteServiceProvider y
| todas tendrán el prefijo "/api".
|
*/

// Esta ruta (ya existente) es para que tu frontend obtenga los datos del usuario
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//  Grupo protegido por Sanctum 
Route::middleware('auth:sanctum')->group(function () {

    // La URL final será: PATCH /api/takes/{take}/complete
    Route::patch('/takes/{take}/complete', [TakeController::class, 'complete']);

    // Si necesitas otras rutas de API (por ejemplo: saltar toma), agrégalas aquí.
    // Route::patch('/takes/{take}/skip', [TakeController::class, 'skip']);
});
