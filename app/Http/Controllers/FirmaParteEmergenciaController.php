<?php

namespace App\Http\Controllers;

use App\Jobs\FirmarDocumentoJob;
use App\Models\Inspeccion;
use App\Models\ParteEmergencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FirmaParteEmergenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $year = $request->filled('year') && is_numeric($request->year)
            ? (int) $request->year
            : now()->year;

        $partesEmergencias = ParteEmergencia::with([
                'parroquia:id,nombre',
                'firmantes.personal'
            ])
            ->whereHas('firmantes', function ($q) {
                $q->where('personal_id', Auth::user()->personal?->id)
                ->whereIn('estado_firma', ['PENDIENTE','ERROR_FIRMA','REQUIERE_PASSWORD']);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('numero', 'like', "%{$search}%")
                    ->orWhere('categoria1', 'like', "%{$search}%")
                    ->orWhere('categoria2', 'like', "%{$search}%");
                });
            })
            ->whereYear('fecha_llamada', $year)
            ->orderByDesc('fecha_llamada')
            ->orderByDesc('secuencia')
            ->paginate(10)
            ->withQueryString();

        return inertia('partesEmergencias/firmar', [
            'partesEmergencias' => $partesEmergencias,
            'filters' => [
                'search' => $search,
                'year' => $year,
            ],
        ]);
    }

    public function firmar(Request $request, ParteEmergencia $parteEmergencia)
    {
        $firmante = $parteEmergencia->firmantes()
            ->where('personal_id', Auth::user()->personal?->id)
            ->whereIn('estado_firma', ['PENDIENTE','ERROR_FIRMA','REQUIERE_PASSWORD'])
            ->first();

        if (!$firmante) {
            return back()->with('error', 'No tiene permisos para firmar este documento');
        }

        // Verificar si hay firmantes anteriores pendientes
        $firmantesPendientesAntes = $parteEmergencia->firmantes()
            ->where('orden_firma', '<', $firmante->orden_firma)
            ->where('estado_firma', '!=', 'FIRMADO')
            ->exists();

        if ($firmantesPendientesAntes) {
            return back()->with('error', 'Debe firmar primero el firmante que le antecede');
        }

        try {

            FirmarDocumentoJob::dispatch(
                \App\Models\ParteEmergencia::class,
                $parteEmergencia->id,
                $request->password
            )->onQueue('firmas');

            return back()->with('success', 'El documento se está firmando en segundo plano');

        } catch (\Exception $e) {

            $firmante->update([
                'estado_firma' => 'ERROR_FIRMA',
                'mensaje_error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al firmar el documento');
        }
    }    
}
