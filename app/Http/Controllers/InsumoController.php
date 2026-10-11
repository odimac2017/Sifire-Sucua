<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use Illuminate\Http\Request;
use App\Http\Requests\ValidarInsumoRequest;

class InsumoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $insumos = Insumo::query()
        ->withCount('partesEmergenciasInsumos')
        ->when($search, function ($query) use ($search) {
            $query->where('nombre', 'like', "%{$search}%");
        })
        ->orderBy('nombre')
        ->paginate(10)
        ->withQueryString();

        return inertia('insumos/index', [
            'insumos' => $insumos,
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
        return inertia('insumos/create', [
            'filters' => [
                'search' => trim($request->input('search', '')),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ValidarInsumoRequest $request)
    {
        $validated = $request->validated();

        Insumo::create($validated);

        return redirect()->route('insumos.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Insumo creado correctamente');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Insumo $insumo)
    {
        return inertia('insumos/edit', [
            'insumo' => $insumo,
            'filters' => [
                'search' => trim($request->input('search', '')),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ValidarInsumoRequest $request, Insumo $insumo)
    {
        $validated = $request->validated();

        $insumo->update($validated);

        return redirect()->route('insumos.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Insumo actualizado correctamente');
    }

    public function inactivar(Request $request, Insumo $insumo)
    {
        $insumo->update([
            'estado' => 'INACTIVO'
        ]);

        return redirect()->route('insumos.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Insumo inactivado correctamente');
    }

    public function reactivar(Request $request, Insumo $insumo)
    {
        $insumo->update([
            'estado' => 'ACTIVO'
        ]);

        return redirect()->route('insumos.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Insumo reactivado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Insumo $insumo)
    {
        $insumo->delete();

        return redirect()->route('inspecciones.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Insumo eliminado correctamente');
    }
}
