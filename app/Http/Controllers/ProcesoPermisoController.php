<?php

namespace App\Http\Controllers;

use App\Models\ProcesoPermiso;
use App\Models\ProcesoPermisoTarifario;

class ProcesoPermisoController extends Controller
{
    public function ultimoProceso($actividadId)
    {   
        $proceso = ProcesoPermiso::where('actividad_economica_id', $actividadId)
            ->latest()
            ->first();

        if (!$proceso) {
            return response()->json(null);
        }

        return response()->json([
            'tipo_permiso_id' => $proceso->tipo_permiso_id,
            'tarifarios' => ProcesoPermisoTarifario::where('proceso_permiso_id', $proceso->id)
                ->pluck('tarifario_id')
                ->toArray(),
        ]);
    }

    public function Finished()
    {
        // CERRAR PROCESOS ABIERTOS DE INSPECCIONES VEHICULARES
        $actualizados = ProcesoPermiso::whereNull('finished_at')
            ->whereYear('created_at', '<', now()->year)
            ->whereHas('tipoPermiso', function ($query) {
                $query->where('tipo_inspeccion_id', 2);
            })
            ->update([
                'finished_at' => now(),
            ]);

        return response()->json([
            'message' => 'Procesos finalizados correctamente.',
            'total' => $actualizados,
        ]);
    }
}
