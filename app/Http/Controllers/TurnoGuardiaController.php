<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ValidarTurnoGuardiaRequest;
use App\Models\PersonalPuesto;
use App\Models\TurnoGuardia;
use Illuminate\Support\Facades\Auth;

class TurnoGuardiaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $turnosGuardias = TurnoGuardia::query()
        ->when($search, function ($query) use ($search) {
            $query->where('nombre', 'like', "%{$search}%");
        })
        ->orderBy('nombre')
        ->paginate(10)
        ->withQueryString();

        return inertia('turnosGuardias/index', [
            'turnosGuardias' => $turnosGuardias,
            'filters' => [
                'search' => $search
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $personalesPuestos = PersonalPuesto::with(['personal.titulo', 'puesto'])
            ->where('personal_id', '!=', Auth::user()->personal->id)
            ->whereHas('personal', function ($q) {
                $q->where('estado', 'ACTIVO');
            })
            ->where('predeterminado', 1)
            ->join('personal', 'personal.id', 'personal_puestos.personal_id')
            ->orderBy('personal.apellidos')
            ->orderBy('personal.nombres')
            ->select('personal_puestos.*')
            ->get()
            ->map(function ($pp) {
                return [
                    'id' => $pp->id,
                    'nombre' => strtoupper(trim("{$pp->personal->nombre}, {$pp->puesto->nombre}")),
                ];
            });

        return inertia('turnosGuardias/create', [
            'personalesPuestos' => $personalesPuestos,
            'filters' => [
                'search' => trim($request->input('search', '')),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ValidarTurnoGuardiaRequest $request)
    {
        $validated = $request->validated();

        $personalPuestoOficial = PersonalPuesto::with(['personal.titulo','puesto'])
        ->findOrFail($validated['personal_puesto_oficial_id']);

        $validated['nombre_oficial'] = strtoupper($personalPuestoOficial->personal->nombre);
        $validated['puesto_oficial'] = strtoupper($personalPuestoOficial->puesto->nombre);

        TurnoGuardia::create($validated);

        return redirect()->route('turnosguardias.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Turno de guardia creado correctamente');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, TurnoGuardia $turnoGuardia)
    {
        $personalesPuestos = PersonalPuesto::with(['personal.titulo', 'puesto'])
            ->where('personal_id', '!=', Auth::user()->personal->id)
            ->whereHas('personal', function ($q) {
                $q->where('estado', 'ACTIVO');
            })
            ->where('predeterminado', 1)
            ->join('personal', 'personal.id', 'personal_puestos.personal_id')
            ->orderBy('personal.apellidos')
            ->orderBy('personal.nombres')
            ->select('personal_puestos.*')
            ->get()
            ->map(function ($pp) {
                return [
                    'id' => $pp->id,
                    'nombre' => strtoupper(trim("{$pp->personal->nombre}, {$pp->puesto->nombre}")),
                ];
            });

        return inertia('turnosGuardias/edit', [
            'personalesPuestos' => $personalesPuestos,
            'turnoGuardia' => $turnoGuardia,
            'filters' => [
                'search' => trim($request->input('search', '')),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ValidarTurnoGuardiaRequest $request, TurnoGuardia $turnoGuardia)
    {
        $validated = $request->validated();

        $personalPuestoOficial = PersonalPuesto::with(['personal.titulo','puesto'])
        ->findOrFail($validated['personal_puesto_oficial_id']);

        $validated['nombre_oficial'] = strtoupper($personalPuestoOficial->personal->nombre);
        $validated['puesto_oficial'] = strtoupper($personalPuestoOficial->puesto->nombre);

        $turnoGuardia->update($validated);

        return redirect()->route('turnosguardias.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Turno de guardia actualizado correctamente');
    }

    public function inactivar(Request $request, TurnoGuardia $turnoGuardia)
    {
        $turnoGuardia->update([
            'estado' => 'INACTIVO'
        ]);

        return redirect()->route('turnosguardias.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Turno de guardia inactivado correctamente');
    }

    public function reactivar(Request $request, TurnoGuardia $turnoGuardia)
    {
        $turnoGuardia->update([
            'estado' => 'ACTIVO'
        ]);

        return redirect()->route('turnosguardias.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Turno de guardia reactivado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, TurnoGuardia $turnoGuardia)
    {
        $turnoGuardia->delete();

        return redirect()->route('turnosguardias.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Turno de guardia eliminado correctamente');
    }
}
