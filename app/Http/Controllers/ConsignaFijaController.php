<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ValidarConsignaFijaRequest;
use App\Models\ConsignaFija;

class ConsignaFijaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $consignasFijas = ConsignaFija::query()
        ->when($search, function ($query) use ($search) {
            $query->where('nombre', 'like', "%{$search}%");
        })
        ->orderBy('nombre')
        ->paginate(10)
        ->withQueryString();

        return inertia('consignasFijas/index', [
            'consignasFijas' => $consignasFijas,
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
        return inertia('consignasFijas/create', [
            'filters' => [
                'search' => trim($request->input('search', '')),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ValidarConsignaFijaRequest $request)
    {
        $validated = $request->validated();

        ConsignaFija::create($validated);

        return redirect()->route('consignasfijas.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Consigna creada correctamente');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, ConsignaFija $consignaFija)
    {
        return inertia('consignasFijas/edit', [
            'consignaFija' => $consignaFija,
            'filters' => [
                'search' => trim($request->input('search', '')),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ValidarConsignaFijaRequest $request, ConsignaFija $consignaFija)
    {
        $validated = $request->validated();
        
        $consignaFija->update($validated);

        return redirect()->route('consignasfijas.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Conigna actualizada correctamente');
    }

    public function inactivar(Request $request, ConsignaFija $consignaFija)
    {
        $consignaFija->update([
            'estado' => 'INACTIVO'
        ]);

        return redirect()->route('consignasfijas.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Consigna inactivada correctamente');
    }

    public function reactivar(Request $request, ConsignaFija $consignaFija)
    {
        $consignaFija->update([
            'estado' => 'ACTIVO'
        ]);

        return redirect()->route('consignasfijas.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Consigna reactivada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, ConsignaFija $consignaFija)
    {
        $consignaFija->delete();

        return redirect()->route('consignasfijas.index', [
            'search' => trim($request->input('search', '')),
        ])->with('success', 'Consigna eliminada correctamente');
    }
}
