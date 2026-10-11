<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidarPermisoFuncionamientoArchivoRequest;
use App\Models\PermisoFuncionamiento;
use App\Models\PermisoFuncionamientoArchivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PermisoFuncionamientoArchivoController extends Controller
{
    public function store(ValidarPermisoFuncionamientoArchivoRequest $request, PermisoFuncionamiento $permisoFuncionamiento)
    {
        $file = $request->file('archivo');

        $ruta = $file->store('permisos_funcionamientos', 'public');

        $permisoFuncionamiento->archivos()->create([
            'nombre' => $file->getClientOriginalName(),
            'ruta' => $ruta,
            'tipo' => $file->getClientOriginalExtension() === 'pdf' ? 'pdf' : 'imagen',
            'size' => $file->getSize(),
        ]);

        if ($permisoFuncionamiento->firmantes()->exists()) {
            $permisoFuncionamiento->estado_firma = 'PENDIENTE';
            $permisoFuncionamiento->save();
        }  

        return redirect()->route('permisosfuncionamientos.show', [
            'permisoFuncionamiento' => $permisoFuncionamiento,
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
            'estado' => $request->input('estado', 'APROBADO'),
        ])->with('success', 'Archivo subido correctamente');
    }

    public function destroy(Request $request, PermisoFuncionamientoArchivo $permisoFuncionamientoArchivo)
    {
        if ($permisoFuncionamientoArchivo->ruta) {
            Storage::disk('public')->delete($permisoFuncionamientoArchivo->ruta);
        }

        $permisoFuncionamiento = $permisoFuncionamientoArchivo->permisoFuncionamiento;

        if ($permisoFuncionamiento->firmantes()->exists()) {
            $permisoFuncionamiento->estado_firma = 'PENDIENTE';
            $permisoFuncionamiento->save();
        }
        
        $permisoFuncionamientoArchivo->delete();

        return redirect()->route('permisosfuncionamientos.show', [
            'permisoFuncionamiento' => $permisoFuncionamiento,
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
            'estado' => $request->input('estado', 'APROBADO'),
        ])->with('success', 'Archivo eliminado');
    }
}
