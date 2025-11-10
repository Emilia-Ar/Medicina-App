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

// El dashboard principal mostrará las tomas del día
Route::get('/dashboard', function () {
    
    // Obtenemos todas las tomas de hoy para el usuario logueado
    $takesToday = \App\Models\Take::where('user_id', auth()->id())
        ->whereDate('scheduled_at', today())
        ->with('medication') // Cargamos la info del medicamento
        ->orderBy('scheduled_at', 'asc')
        ->get();

    // Agrupamos las tomas por medicamento
    $medicationsToday = $takesToday->groupBy('medication.name');

    // Alerta de stock bajo
    $lowStockMedications = Medication::where('user_id', auth()->id())
        ->where('current_stock', '<=', 2) // Avisa cuando queden 2 o menos
        ->get();

    return view('dashboard', [
        'medicationsToday' => $medicationsToday,
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

