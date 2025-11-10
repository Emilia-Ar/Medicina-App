<?php

namespace App\Http\Controllers;

use App\Models\Medication;
use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class MedicationController extends Controller
{
    protected $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Muestra la lista de medicamentos del usuario.
     */
    public function index()
    {
        return redirect()->route('dashboard');
    }

    /**
     * Muestra el formulario para crear un nuevo medicamento.
     */
    public function create()
    {
        return view('medications.create');
    }

    /**
     * Guarda el nuevo medicamento en la base de datos.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:1024',
            'total_stock' => 'required|integer|min:1',
            'stock_unit' => 'required|string|max:50',
            'dose_quantity' => 'required|integer|min:1',
            'dose_type' => 'required|string|in:unit,half,quarter,drop',
            'frequency_hours' => 'required|integer|in:4,6,8,12,24',
            'start_time' => 'required|date_format:H:i',
        ]);

        $path = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
        }

        $medication = Medication::create([
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'description' => $data['description'],
            'photo_path' => $path,
            'total_stock' => $data['total_stock'],
            'current_stock' => $data['total_stock'],
            'dose_quantity' => $data['dose_quantity'],
            'frequency_hours' => $data['frequency_hours'],
            'start_time' => $data['start_time'],
        ]);

        $this->scheduleService->generateTakes($medication);

        return redirect()->route('dashboard')->with('status', '¡Medicamento añadido con éxito!');
    }

    /**
     * Muestra la vista detallada (checklist de tomas diarias).
     */
    public function show(Medication $medication)
    {
        if ($medication->user_id !== auth()->id()) {
            abort(403);
        }

        $todaysTakes = $medication->takes()
            ->whereDate('scheduled_at', today())
            ->orderBy('scheduled_at', 'asc')
            ->get();

        $pastPendingTakes = $medication->takes()
            ->where('scheduled_at', '<', today()->startOfDay())
            ->whereNull('completed_at')
            ->orderBy('scheduled_at', 'desc')
            ->get();

        return view('medications.show', [
            'medication' => $medication,
            'todaysTakes' => $todaysTakes,
            'pastPendingTakes' => $pastPendingTakes,
        ]);
    }

    /**
     * Muestra el formulario para editar un medicamento.
     */
    public function edit(Medication $medication)
    {
        if ($medication->user_id !== auth()->id()) {
            abort(403);
        }

        return view('medications.edit', [
            'medication' => $medication,
        ]);
    }

    /**
     * Actualiza el medicamento en la base de datos.
     */
    public function update(Request $request, Medication $medication)
    {
        // 1. Política de seguridad
        if ($medication->user_id !== auth()->id()) {
            abort(403);
        }

        // 2. Validación
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:1024',
            'dose_quantity' => 'required|integer|min:1',
            'dose_type' => 'required|string|in:unit,half,quarter,drop',
            'frequency_hours' => 'required|integer|in:4,6,8,12,24',
            'start_time' => 'required|date_format:H:i',
        ]);

        // 3. Comprobar si el horario cambió
        $newStartTime = Carbon::parse($data['start_time'])->format('H:i:s');
        $oldStartTime = Carbon::parse($medication->start_time)->format('H:i:s');

        // Lógica de comparación estricta
        $scheduleChanged = ($medication->frequency_hours !== (int) $data['frequency_hours'] || $oldStartTime !== $newStartTime);

        // 4. Manejar la subida de la nueva foto
        if ($request->hasFile('photo')) {
            if ($medication->photo_path) {
                Storage::disk('public')->delete($medication->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('photos', 'public');
        }

        // 5. Actualizar el modelo (primero)
        $medication->update($data);

        // 6. Lógica de Recalcular Calendario
        if ($scheduleChanged) {

            // Borramos TODAS las tomas pendientes (pasadas y futuras)
            $medication->takes()
                ->whereNull('completed_at') // <-- ¡Corrección!
                ->delete();

            // Pedimos al servicio que genere el nuevo calendario
            $this->scheduleService->generateTakes($medication);
        }

        // 7. Redirigir
        return redirect()
            ->route('medications.show', $medication)
            ->with('status', '¡Medicamento actualizado con éxito!');
    }

    /**
     * Genera y descarga un reporte PDF de las tomas de un medicamento.
     * (ESTE MÉTODO HA SIDO ACTUALIZADO)
     */
    public function downloadReport(Request $request, Medication $medication)
    {
        // 1. Seguridad
        if ($medication->user_id !== auth()->id()) {
            abort(403, 'Acción no autorizada.');
        }

        // 2. Validación de fechas
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        // 3. Obtener las tomas
        $takes = $medication->takes()
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->orderBy('scheduled_at', 'asc')
            ->get();

        // 4. Calcular estadísticas base
        $stats = [
            'total' => $takes->count(),
            'completed' => $takes->whereNotNull('completed_at')->count(),
            'missed' => $takes->whereNull('completed_at')->count(),
        ];

        // 5. ¡NUEVO! Calcular Tasa de Cumplimiento
        $complianceRate = 0;
        if ($stats['total'] > 0) {
            $complianceRate = round(($stats['completed'] / $stats['total']) * 100);
        }

        // 6. ¡NUEVO! Cargar el logo en Base64
        $logoPath = public_path('images/logo-medicina.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = base64_encode($logoData);
        }

        // 7. Preparar todos los datos para la vista
        $data = [
            'medication' => $medication,
            'takes' => $takes,
            'stats' => $stats,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'logoBase64' => $logoBase64,       // <-- Dato nuevo
            'user' => auth()->user(),
            'complianceRate' => $complianceRate, // <-- Dato nuevo
        ];

        // 8. Cargar el PDF
        $pdf = Pdf::loadView('reports.medication', $data);
        $fileName = 'reporte-' . Str::slug($medication->name) . '-' .
            $startDate->format('Y-m-d') . '-al-' . $endDate->format('Y-m-d') . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Aumenta el stock de un medicamento existente.
     */
    public function addStock(Request $request, Medication $medication)
    {
        if ($medication->user_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'new_stock_quantity' => 'required|integer|min:1',
        ]);

        $newStock = $data['new_stock_quantity'];

        $medication->update([
            'current_stock' => $medication->current_stock + $newStock,
            'total_stock' => $medication->total_stock + $newStock,
        ]);

        return back()->with('status', "¡Stock de {$medication->name} actualizado con éxito!");
    }

    /**
     * Elimina un medicamento.
     */
    public function destroy(Medication $medication)
    {
        if ($medication->user_id !== auth()->id()) {
            abort(403, 'Acción no autorizada.');
        }

        if ($medication->photo_path) {
            Storage::disk('public')->delete($medication->photo_path);
        }

        $medication->delete();

        return redirect()->route('dashboard')->with('status', "¡Medicamento '{$medication->name}' eliminado con éxito!");
    }

    public function useStock(Request $request, Medication $medication)
    {
        if ($medication->user_id !== auth()->id()) {
            abort(403);
        }

        // Solo descontamos si el stock actual es mayor que 0
        if ($medication->current_stock > 0) {
            $medication->update([
                'current_stock' => $medication->current_stock - 1
            ]);
        }

        return back()->with('status', "¡Se ha registrado el uso de 1 {$medication->stock_unit}!");
    }
}