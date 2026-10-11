<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ValidarFuncionConsignaRequest;
use App\Models\FuncionConsigna;

class FuncionConsignaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $funcionesConsignas = FuncionConsigna::query()
        ->when($search, function ($query) use ($search) {
            $query->where('nombre', 'like', "%{$search}%");
        })
        ->orderBy('nombre')
        ->paginate(10)
        ->withQueryString();

        return inertia('funcionesConsignas/index', [
            'funcionesConsignas' => $funcionesConsignas,
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
        return inertia('funcionesConsignas/create', [
            'filters' => [
                'search' => trim($request->input('search', '')),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ValidarFuncionConsignaRequest $request)
    {
        $validated = $request->validated();

        FuncionConsigna::create($validated);

        return redirect()->route('funcionesconsignas.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Función creada correctamente');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, FuncionConsigna $funcionConsigna)
    {
        return inertia('funcionesConsignas/edit', [
            'funcionConsigna' => $funcionConsigna,
            'filters' => [
                'search' => trim($request->input('search', '')),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ValidarFuncionConsignaRequest $request, FuncionConsigna $funcionConsigna)
    {
        $validated = $request->validated();
        
        $funcionConsigna->update($validated);

        return redirect()->route('funcionesconsignas.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Función actualizada correctamente');
    }

    public function inactivar(Request $request, FuncionConsigna $funcionConsigna)
    {
        $funcionConsigna->update([
            'estado' => 'INACTIVO'
        ]);

        return redirect()->route('funcionesconsignas.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Función inactivada correctamente');
    }

    public function reactivar(Request $request, FuncionConsigna $funcionConsigna)
    {
        $funcionConsigna->update([
            'estado' => 'ACTIVO'
        ]);

        return redirect()->route('funcionesconsignas.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Función reactivada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, FuncionConsigna $funcionConsigna)
    {
        $funcionConsigna->delete();

        return redirect()->route('funcionesconsignas.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Función eliminada correctamente');
    }
}
