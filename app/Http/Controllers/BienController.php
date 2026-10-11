<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ValidarBienRequest;
use App\Models\Bien;
use App\Models\ClasificacionBien;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BienController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $estado = $request->filled('estado') && $request->estado !== 'ALL'
            ? $request->estado
            : null;

        $bienes = Bien::with([
            'clasificacionBien'
        ])
        ->when($search, function ($query) use ($search) {
            $query->where('codigo_inventario', 'like', "%{$search}%")
                ->orWhere('marca', 'like', "%{$search}%")
                ->orWhere('modelo', 'like', "%{$search}%")
                ->orWhere('serie', 'like', "%{$search}%")
                ->orWhere('placa', 'like', "%{$search}%");
        })
        ->when($estado === 'USO', function ($query) {
            $query->whereIn('estado', [
                'BUENO',
                'REGULAR',
                'MALO',
                'OBSOLETO'
            ]);
        })
        ->when($estado === 'INSERVIBLE', function ($query) {
            $query->whereIn('estado', [
                'PARA BAJA',
                'BAJA'
            ]);
        })
        ->orderBy('clasificacion_bien_id')
        ->orderBy('marca')
        ->orderBy('modelo')
        ->orderBy('serie')        
        ->paginate(10)
        ->withQueryString();

        return inertia('bienes/index', [
            'bienes' => $bienes,
            'filters' => [
                'search' => $search,
                'estado' => $estado ?? 'ALL',
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $estado = $request->filled('estado') && $request->estado !== 'ALL'
        ? $request->estado
        : null;

        $clasificacionesBienes = ClasificacionBien::select('id','nombre')
            ->where('estado', 'ACTIVO')
            ->get();

        return inertia('bienes/create', [
            'clasificacionesBienes' => $clasificacionesBienes,
            'filters' => [
                'search' => trim($request->input('search', '')),
                'estado' => $estado ?? 'ALL'
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ValidarBienRequest $request)
    {
        $estado = $request->filled('estado') && $request->estado !== 'ALL'
        ? $request->estado
        : null;

        $validated = $request->validated();

        $validated['estado'] = $validated['estado_bien'];

        // Guardar imagen
        if ($request->hasFile('foto')) {

            $extension = $request->file('foto')->getClientOriginalExtension();

            $nombreFoto = 'bien_' . Str::uuid() . '.' . $extension;

            $validated['ruta_foto'] = $request->file('foto')->storeAs(
                'bienes',
                $nombreFoto,
                'public'
            );
        }

        Bien::create($validated);

        return redirect()->route('bienes.index', [
            'search' => trim($request->input('search', '')),
            'estado' => $estado ?? 'ALL'
        ])->with('success', 'Bien creado correctamente');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Bien $bien)
    {
        $estado = $request->filled('estado') && $request->estado !== 'ALL'
            ? $request->estado
            : null;

        $clasificacionesBienes = ClasificacionBien::select('id','nombre')
            ->where('estado', 'ACTIVO')
            ->get();

        return inertia('bienes/edit', [
            'clasificacionesBienes' => $clasificacionesBienes,
            'bien' => $bien,
            'filters' => [
                'search' => trim($request->input('search', '')),
                'estado' => $estado ?? 'ALL'
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ValidarBienRequest $request, Bien $bien)
    {
        $estado = $request->filled('estado') && $request->estado !== 'ALL'
            ? $request->estado
            : null;

        $validated = $request->validated();

        $validated['estado'] = $validated['estado_bien'];

        /*
        |--------------------------------------------------------------------------
        | FOTO
        |--------------------------------------------------------------------------
        */

        // Eliminar foto
        if ($request->ruta_foto === null && $bien->ruta_foto) {

            Storage::disk('public')->delete($bien->ruta_foto);

            $validated['ruta_foto'] = null;
        }

        // Nueva foto
        if ($request->hasFile('foto')) {

            $extension = $request->file('foto')
                ->getClientOriginalExtension();

            // Mantener mismo nombre
            if ($bien->ruta_foto) {

                $nombreArchivo = pathinfo(
                    $bien->ruta_foto,
                    PATHINFO_FILENAME
                ) . '.' . $extension;

                // eliminar foto anterior
                Storage::disk('public')->delete($bien->ruta_foto);

            } else {

                $nombreArchivo = 'bien_' . Str::uuid() . '.' . $extension;
            }

            $validated['ruta_foto'] = $request->file('foto')->storeAs(
                'bienes',
                $nombreArchivo,
                'public'
            );
        }
        
        $bien->update($validated);

        return redirect()->route('bienes.index', [
            'search' => trim($request->input('search', '')),
            'estado' => $estado ?? 'ALL'
        ])->with('success', 'Bien actualizado correctamente');
    }

    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Bien $bien)
    {
        $estado = $request->filled('estado') && $request->estado !== 'ALL'
            ? $request->estado
            : null;

        $bien->delete();

        return redirect()->route('bienes.index', [
            'search' => trim($request->input('search', '')),
            'estado' => $estado ?? 'ALL'
        ])->with('success', 'Bien eliminado correctamente');
    }
}
