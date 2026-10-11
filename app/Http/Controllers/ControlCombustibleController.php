<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ValidarControlCombustibleRequest;
use App\Http\Requests\ValidarControlCombustibleRequest as RequestsValidarControlCombustibleRequest;
use App\Http\Requests\ValidarControlCombustibleRequest as HttpRequestsValidarControlCombustibleRequest;
use App\Http\Requests\ValidarControlCombustibleRequest as AppHttpRequestsValidarControlCombustibleRequest;
use App\Models\Bien;
use App\Models\Combustible;
use App\Models\Configuracion;
use App\Models\ControlCombustible;
use App\Models\Personal;

class ControlCombustibleController extends Controller
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
        $estado = $request->filled('estado') && $request->estado !== 'ALL'
            ? $request->estado
            : null;

        $controlesCombustibles = ControlCombustible::with([
            'bien',
            'combustible',
        ])
        ->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('bien', function ($qb) use ($search) {
                    $qb->where('identificacion', 'like', "%{$search}%");
                })
                ->orWhere('nombre_chofer', 'like', "%{$search}%");
            });
        })
        ->when($estado, function ($query) use ($estado) {
            $query->where('estado', $estado);
        })
        ->whereYear('fecha', $year)
        ->orderBy('numero')
        ->orderBy('fecha')
        ->paginate(10)
        ->withQueryString();

        return inertia('controlesCombustibles/index', [
            'controlesCombustibles' => $controlesCombustibles,
            'filters' => [
                'search' => $search,
                'year' => $year,
                'estado' => $estado ?? 'ALL',
            ],
        ]);
    }

    public function create(Request $request)
    {
        $bienes = Bien::where('tipo_bien', 'V')
            ->where('control_combustible', 1)
            ->where('estado', '!=', 'BAJA')
            ->orderBy('tipo_vehiculo')
            ->orderBy('identificacion')
            ->get();

        $personales = Personal::whereHas('user.roles', function($query) {
                $query->where('rol_id', 10);
            })
            ->where('estado', 'ACTIVO')
            ->get();

        $combustibles = Combustible::select('id','nombre')
            ->where('estado', 'ACTIVO')
            ->get();

        $numero = (ControlCombustible::whereYear('fecha', now()->year)
            ->max('numero') ?? 0) + 1;

        $configuracion = Configuracion::first();
        
        return inertia('controlesCombustibles/create', [
            'bienes' => $bienes,
            'personales' => $personales,
            'combustibles' => $combustibles,
            'numero' => str_pad($numero,7,'0',STR_PAD_LEFT),
            'porcentaje_iva' => $configuracion->porcentaje_iva ?? 0,
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => (int) $request->input('year', now()->year),
                'estado' => $request->input('estado', 'GENERADO'),
            ],
        ]);
    }

    public function store(ValidarControlCombustibleRequest $request)
    {
        $personal = Personal::where('id', $request->personal_id)
        ->firstOrFail();

        $validated = $request->validated();

        $validated['nombre_chofer'] = strtoupper($personal->nombre);
        $validated['numero'] = strtoupper($request->numero);
        $validated['subtotal'] = strtoupper($request->subtotal);
        $validated['porcentaje_iva'] = strtoupper($request->porcentaje_iva);
        $validated['valor_iva'] = strtoupper($request->valor_iva);
        $validated['valor_total'] = strtoupper($request->valor_total);

        ControlCombustible::create($validated);

        return redirect()->route('controlescombustibles.index', [
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
        ])->with('success', 'Control de combustible creado correctamente');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, ControlCombustible $controlCombustible)
    {
        $bienes = Bien::where('tipo_bien', 'V')
            ->where('control_combustible', 1)
            ->where('estado', '!=', 'BAJA')
            ->orderBy('tipo_vehiculo')
            ->orderBy('identificacion')
            ->get();

        $personales = Personal::whereHas('user.roles', function($query) {
                $query->where('rol_id', 10);
            })
            ->where('estado', 'ACTIVO')
            ->get();

        $combustibles = Combustible::select('id','nombre')
            ->where('estado', 'ACTIVO')
            ->get();
        
        $configuracion = Configuracion::first();

        return inertia('controlesCombustibles/edit', [
            'bienes' => $bienes,
            'personales' => $personales,
            'combustibles' => $combustibles,
            'porcentaje_iva' => $configuracion->porcentaje_iva ?? 0,
            'controlCombustible' => $controlCombustible,
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => (int) $request->input('year', now()->year),
                'estado' => $request->input('estado', 'GENERADO'),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ValidarControlCombustibleRequest $request, ControlCombustible $controlCombustible)
    {
        $personal = Personal::where('id', $request->personal_id)
        ->firstOrFail();

        $validated = $request->validated();

        $validated['nombre_chofer'] = strtoupper($personal->nombre);
        $validated['subtotal'] = strtoupper($request->subtotal);
        $validated['porcentaje_iva'] = strtoupper($request->porcentaje_iva);
        $validated['valor_iva'] = strtoupper($request->valor_iva);
        $validated['valor_total'] = strtoupper($request->valor_total);

        $controlCombustible->update($validated);

        return redirect()->route('controlescombustibles.index', [
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
        ])->with('success', 'Control de combustible actualizado correctamente');
    }

    public function anular(Request $request, ControlCombustible $controlCombustible)
    {
        $controlCombustible->update([
            'estado' => 'ANULADO'
        ]);

        return redirect()->route('controlescombustibles.index', [
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
            'estado' => $request->input('estado', 'GENERADO'),
        ])->with('success', 'Control de combustible anulado correctamente');
    }

    

}
