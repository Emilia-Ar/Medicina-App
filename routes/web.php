<?php

use App\Http\Controllers\MedicationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TakeController;
use App\Http\Controllers\PushSubscriptionController; // ✅ Añadido para las notificaciones
use App\Models\Medication;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    // Si el usuario está logueado, llévalo al dashboard.
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    
    
    // Si no está logueado, llévalo a la nueva vista de bienvenida.
    return view('welcome'); 
    
});

// --- BLOQUE CORREGIDO ---
// El dashboard principal ahora carga TODOS los medicamentos
Route::get('/dashboard', function () {
    
    // 1. Obtenemos TODOS los medicamentos del usuario
    // La vista 'dashboard.blade.php' espera esta variable
    $medications = Medication::where('user_id', auth()->id())
        ->orderBy('name')
        ->get();

    // 2. Obtenemos los medicamentos con STOCK BAJO (para la alerta)
    // (Solo contamos los "contables" como pastillas)
    $contableTypes = ['unit', 'half', 'quarter'];
    $lowStockMedications = $medications // Filtramos la colección que ya obtuvimos
        ->whereIn('dose_type', $contableTypes)
        ->where('current_stock', '<=', 2); // Mantenemos tu lógica original de <= 2

    // 3. Pasamos las variables correctas a la vista
    return view('dashboard', [
        'medications' => $medications, // <-- La variable que la vista espera
        'lowStockMedications' => $lowStockMedications,
    ]);

})->middleware(['auth', 'verified'])->name('dashboard');



// Rutas de Perfil (de Breeze) + Medicamentos + Takes + Reporte + Push
Route::middleware('auth')->group(function () {
    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // RUTA PARA AÑADIR STOCK
    Route::patch('/medications/{medication}/add-stock', [MedicationController::class, 'addStock'])->name('medications.addStock');
    Route::patch('/medications/{medication}/use-stock', [MedicationController::class, 'useStock'])->name('medications.useStock');

    // Medicamentos (CRUD)
    Route::resource('medications', MedicationController::class);
    
    Route::patch('/takes/{take}/complete', [TakeController::class, 'complete'])->name('takes.complete');
    

    // RUTA PARA EL REPORTE
    Route::get('/medications/{medication}/report', [MedicationController::class, 'downloadReport'])
        ->name('medications.report');

    // ✅ RUTAS PARA SUSCRIPCIONES PUSH
    Route::post('/push-subscribe', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::post('/push-unsubscribe', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');
});

require __DIR__.'/auth.php';

