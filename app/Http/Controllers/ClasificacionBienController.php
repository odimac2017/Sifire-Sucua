<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ValidarClasificacionBienRequest;
use App\Models\ClasificacionBien;

class ClasificacionBienController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $clasificacionesBienes = ClasificacionBien::query()
        ->withCount('bienes')
        ->when($search, function ($query) use ($search) {
            $query->where('nombre', 'like', "%{$search}%");
        })
        ->orderBy('nombre')
        ->paginate(10)
        ->withQueryString();

        return inertia('clasificacionesBienes/index', [
            'clasificacionesBienes' => $clasificacionesBienes,
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
        return inertia('clasificacionesBienes/create', [
            'filters' => [
                'search' => trim($request->input('search', '')),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ValidarClasificacionBienRequest $request)
    {
        $validated = $request->validated();

        ClasificacionBien::create($validated);

        return redirect()->route('clasificacionesbienes.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Clasificación creada correctamente');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, ClasificacionBien $clasificacionBien)
    {
        return inertia('clasificacionesBienes/edit', [
            'clasificacionBien' => $clasificacionBien,
            'filters' => [
                'search' => trim($request->input('search', '')),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ValidarClasificacionBienRequest $request, ClasificacionBien $clasificacionBien)
    {
        $validated = $request->validated();
        
        $clasificacionBien->update($validated);

        return redirect()->route('clasificacionesbienes.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Clasificación actualizada correctamente');
    }

    public function inactivar(Request $request, ClasificacionBien $clasificacionBien)
    {
        $clasificacionBien->update([
            'estado' => 'INACTIVO'
        ]);

        return redirect()->route('clasificacionesbienes.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Clasificación inactivada correctamente');
    }

    public function reactivar(Request $request, ClasificacionBien $clasificacionBien)
    {
        $clasificacionBien->update([
            'estado' => 'ACTIVO'
        ]);

        return redirect()->route('clasificacionesbienes.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Clasificación reactivada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, ClasificacionBien $clasificacionBien)
    {
        $clasificacionBien->delete();

        return redirect()->route('clasificacionesbienes.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Clasificación eliminada correctamente');
    }
}
