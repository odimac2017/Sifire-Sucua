<?php

namespace App\Services;

use App\Models\Inspeccion;
use App\Models\ProcesoPermiso;
use App\Data\InspeccionCalculoData;

class InspeccionCalculadoraService
{
    public function calcular(ProcesoPermiso $procesoPermiso, float $valorInspeccion, ?int $inspeccionId = null)
    {
        $valorDescuento = 0;
        $valorPermiso= 0;
        $valorInspeccionesAnteriores = 0;
        $subtotal = 0;
        $valorTotal = 0;
        
        $porcentajeDescuento = $procesoPermiso->cliente->porcentaje_descuento ?? 0;

        $valorDescuento = $valorInspeccion * ($porcentajeDescuento / 100);

        $valorPermiso = ($procesoPermiso->valor_impuesto_predial ?? 0) * 0.00015;

        $valorInspeccionesAnteriores = Inspeccion::when($inspeccionId, function ($q) use ($inspeccionId) {
                $q->where('id', '!=', $inspeccionId);
            })
            ->where('estado', 'APROBADO')
            ->whereHas('procesoPermiso', function ($q) use ($procesoPermiso) {
                $q->whereNull('finished_at')
                ->where('actividad_economica_id', $procesoPermiso->actividad_economica_id);
            })
            ->sum('valor_total');

        $subtotal = $valorInspeccion
            - $valorDescuento
            + $valorPermiso
            + $valorInspeccionesAnteriores;        

        $subtotalInspecciones = Inspeccion::where('proceso_permiso_id', $procesoPermiso->id)
            ->when($inspeccionId, function ($q) use ($inspeccionId) {
                $q->where('id', '!=', $inspeccionId);
            })
            ->sum('subtotal');

        $valorTotal = $subtotal + $subtotalInspecciones;

        return new InspeccionCalculoData(
            valor_inspeccion: $valorInspeccion,
            valor_descuento: $valorDescuento,
            valor_permiso: $valorPermiso,
            valor_inspecciones_anteriores: $valorInspeccionesAnteriores,
            subtotal: $subtotal,
            valor_total: $valorTotal
        );
    }
}