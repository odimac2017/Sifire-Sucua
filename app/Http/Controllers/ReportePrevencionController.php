<?php

namespace App\Http\Controllers;

use App\Models\Inspeccion;
use App\Models\PermisoFuncionamiento;
use App\Models\SolicitudInspeccion;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportePrevencionController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('rango_fecha.from')
            ? Carbon::parse($request->input('rango_fecha.from'))->startOfDay()
            : now()->startOfMonth();

        $to = $request->input('rango_fecha.to')
            ? Carbon::parse($request->input('rango_fecha.to'))->endOfDay()
            : now()->endOfDay();

        if ($from->isSameDay($to)) {
            // 🟢 HOY vs AYER
            $fromPrev = (clone $from)->subDay()->startOfDay();
            $toPrev = (clone $to)->subDay()->endOfDay();

        } elseif ($from->isSameMonth($to) && $from->day === 1) {
            // 🔵 ESTE MES (hasta hoy) vs MISMO PERIODO MES ANTERIOR

            $days = $from->diffInDays($to);

            $fromPrev = (clone $from)->subMonth()->startOfMonth();
            $toPrev = (clone $fromPrev)->addDays($days)->endOfDay();

        } elseif ($from->isSameYear($to) && $from->dayOfYear === 1) {
            // 🟣 ESTE AÑO (hasta hoy) vs MISMO PERIODO AÑO ANTERIOR

            $days = $from->diffInDays($to);

            $fromPrev = (clone $from)->subYear()->startOfYear();
            $toPrev = (clone $fromPrev)->addDays($days)->endOfDay();

        } else {
            // 🔴 RANGO PERSONALIZADO (últimos 6 meses, etc.)
            $diffDays = $from->diffInDays($to);

            $fromPrev = (clone $from)->subDays($diffDays + 1);
            $toPrev = (clone $to)->subDays($diffDays + 1);
        }

        $solicitudesInspeccionesActual = SolicitudInspeccion::whereBetween('created_at', [$from, $to])
        ->where('tipo_solicitud', 'IN')
        ->count();

        $solicitudesInspeccionesAnterior = SolicitudInspeccion::whereBetween('created_at', [$fromPrev, $toPrev])
        ->where('tipo_solicitud', 'IN')
        ->count();
        
         $solicitudesInspeccionesTrend = SolicitudInspeccion::whereBetween('created_at', [$from, $to])
        ->selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
        ->where('tipo_solicitud', 'IN')
        ->groupBy('fecha')
        ->orderBy('fecha')
        ->get();

        $solicitudesReinspeccionesActual = SolicitudInspeccion::whereBetween('created_at', [$from, $to])
        ->where('tipo_solicitud', 'RE')
        ->count();
        
        $solicitudesReinspeccionesAnterior = SolicitudInspeccion::whereBetween('created_at', [$fromPrev, $toPrev])
        ->where('tipo_solicitud', 'RE')
        ->count();
        
        $solicitudesReinspeccionesTrend = SolicitudInspeccion::whereBetween('created_at', [$from, $to])
        ->selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
        ->where('tipo_solicitud', 'RE')
        ->groupBy('fecha')
        ->orderBy('fecha')
        ->get();
        
        $inspeccionesActual = Inspeccion::whereBetween('fecha_inspeccion', [$from, $to])
        ->count();
        
        $inspeccionesAnterior = Inspeccion::whereBetween('fecha_inspeccion', [$fromPrev, $toPrev])
        ->count();
    
        $inspeccionesTrend = Inspeccion::whereBetween('fecha_inspeccion', [$from, $to])
        ->selectRaw('DATE(fecha_inspeccion) as fecha, COUNT(*) as total')
        ->groupBy('fecha')
        ->orderBy('fecha')
        ->get();
        
        $baseQuery1 = PermisoFuncionamiento::whereHas('procesoPermiso.tipoPermiso', function ($q) {
            $q->where('id', 1);
        })
        ->where('estado', 'EXPEDIDO');

        /* PERMISOS ESTABLECIMIENTOS */
        $permisosFuncionamientosActual = (clone $baseQuery1)
            ->whereBetween('fecha_deposito', [$from, $to])
            ->count();

        $permisosFuncionamientosAnterior = (clone $baseQuery1)
            ->whereBetween('fecha_deposito', [$fromPrev, $toPrev])
            ->count();

        $permisosFuncionamientosTrend = (clone $baseQuery1)
            ->whereBetween('fecha_deposito', [$from, $to])
            ->selectRaw('DATE(fecha_deposito) as fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        /* RECAUDACION PERMISOS ESTABLECIMIENTOS */
        $recaudacionPermisosFuncionamientosActual = (clone $baseQuery1)
            ->whereBetween('fecha_deposito', [$from, $to])
            ->sum('valor_total');

        $recaudacionPermisosFuncionamientosAnterior = (clone $baseQuery1)
            ->whereBetween('fecha_deposito', [$fromPrev, $toPrev])
            ->sum('valor_total');

        $recaudacionPermisosFuncionamientosTrend = (clone $baseQuery1)
            ->whereBetween('fecha_deposito', [$from, $to])
            ->selectRaw('DATE(fecha_deposito) as fecha, SUM(valor_total) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
        
        $baseQuery2 = PermisoFuncionamiento::whereHas('procesoPermiso.tipoPermiso', function ($q) {
            $q->where('id', 2);
        })
        ->where('estado', 'EXPEDIDO');

        /* PERMISOS VEHICULOS */
        $permisosFuncionamientosVehiculosActual = (clone $baseQuery2)
            ->whereBetween('fecha_deposito', [$from, $to])
            ->count();

        $permisosFuncionamientosVehiculosAnterior = (clone $baseQuery2)
            ->whereBetween('fecha_deposito', [$fromPrev, $toPrev])
            ->count();

        $permisosFuncionamientosVehiculosTrend = (clone $baseQuery2)
            ->whereBetween('fecha_deposito', [$from, $to])
            ->selectRaw('DATE(fecha_deposito) as fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        /* RECAUDACION PERMISOS VEHICULOS*/
        $recaudacionPermisosFuncionamientosVehiculosActual = (clone $baseQuery2)
            ->whereBetween('fecha_deposito', [$from, $to])
            ->sum('valor_total');

        $recaudacionPermisosFuncionamientosVehiculosAnterior = (clone $baseQuery2)
            ->whereBetween('fecha_deposito', [$fromPrev, $toPrev])
            ->sum('valor_total');

        $recaudacionPermisosFuncionamientosVehiculosTrend = (clone $baseQuery2)
            ->whereBetween('fecha_deposito', [$from, $to])
            ->selectRaw('DATE(fecha_deposito) as fecha, SUM(valor_total) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $baseQuery3 = PermisoFuncionamiento::whereHas('procesoPermiso.tipoPermiso', function ($q) {
            $q->where('id', 3);
        })
        ->where('estado', 'EXPEDIDO');

        /* VISTOS BUENOS PLANOS */
        $permisosFuncionamientosPlanosActual = (clone $baseQuery3)
            ->whereBetween('fecha_deposito', [$from, $to])
            ->count();

        $permisosFuncionamientosPlanosAnterior = (clone $baseQuery3)
            ->whereBetween('fecha_deposito', [$fromPrev, $toPrev])
            ->count();

        $permisosFuncionamientosPlanosTrend = (clone $baseQuery3)
            ->whereBetween('fecha_deposito', [$from, $to])
            ->selectRaw('DATE(fecha_deposito) as fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
        
        $totalPermisosFuncionamientosActual = $permisosFuncionamientosActual + $permisosFuncionamientosVehiculosActual + $permisosFuncionamientosPlanosActual;
        $totalPermisosFuncionamientosAnterior = $permisosFuncionamientosAnterior + $permisosFuncionamientosVehiculosAnterior + $permisosFuncionamientosPlanosAnterior;

        $totalPermisosFuncionamientosTrend = collect()
            ->merge($permisosFuncionamientosTrend)
            ->merge($permisosFuncionamientosVehiculosTrend)
            ->merge($permisosFuncionamientosPlanosTrend)
            ->groupBy('fecha')
            ->map(function ($items) {
                return [
                    'fecha' => $items->first()['fecha'],
                    'total' => $items->sum('total'),
                ];
            })
            ->sortBy('fecha')
            ->values();

        /* RECAUDACION VISTOS BUENOS PLANOS*/
        $recaudacionPermisosFuncionamientosPlanosActual = (clone $baseQuery3)
            ->whereBetween('fecha_deposito', [$from, $to])
            ->sum('valor_total');

        $recaudacionPermisosFuncionamientosPlanosAnterior = (clone $baseQuery3)
            ->whereBetween('fecha_deposito', [$fromPrev, $toPrev])
            ->sum('valor_total');

        $recaudacionPermisosFuncionamientosPlanosTrend = (clone $baseQuery3)
            ->whereBetween('fecha_deposito', [$from, $to])
            ->selectRaw('DATE(fecha_deposito) as fecha, SUM(valor_total) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $totalRecaudacionActual = $recaudacionPermisosFuncionamientosActual + $recaudacionPermisosFuncionamientosVehiculosActual + $recaudacionPermisosFuncionamientosPlanosActual;
        $totalRecaudacionAnterior = $recaudacionPermisosFuncionamientosAnterior + $recaudacionPermisosFuncionamientosVehiculosAnterior + $recaudacionPermisosFuncionamientosPlanosAnterior;

        $totalRecaudacionTrend = collect()
            ->merge($recaudacionPermisosFuncionamientosTrend)
            ->merge($recaudacionPermisosFuncionamientosVehiculosTrend)
            ->merge($recaudacionPermisosFuncionamientosPlanosTrend)
            ->groupBy('fecha')
            ->map(function ($items) {
                return [
                    'fecha' => $items->first()['fecha'],
                    'total' => $items->sum('total'),
                ];
            })
            ->sortBy('fecha')
            ->values();

        return inertia('reportes/prevenciones', [
            'totales' => [
                'solicitudesInspecciones' => [
                    'total' => $solicitudesInspeccionesActual,
                    'growth' => $this->crecimiento($solicitudesInspeccionesActual, $solicitudesInspeccionesAnterior),
                    'trend' => $solicitudesInspeccionesTrend,
                ],
                'solicitudesReinspecciones' => [
                    'total' => $solicitudesReinspeccionesActual,
                    'growth' => $this->crecimiento($solicitudesReinspeccionesActual, $solicitudesReinspeccionesAnterior),
                    'trend' => $solicitudesReinspeccionesTrend,
                ],
                'inspecciones' => [
                    'total' => $inspeccionesActual,
                    'growth' => $this->crecimiento($inspeccionesActual, $inspeccionesAnterior),
                    'trend' => $inspeccionesTrend,
                ],
                'permisosFuncionamientos' => [
                    'total' => $permisosFuncionamientosActual,
                    'growth' => $this->crecimiento($permisosFuncionamientosActual, $permisosFuncionamientosAnterior),
                    'trend' => $permisosFuncionamientosTrend,
                ],
                'permisosFuncionamientosVehiculos' => [
                    'total' => $permisosFuncionamientosVehiculosActual,
                    'growth' => $this->crecimiento($permisosFuncionamientosVehiculosActual, $permisosFuncionamientosVehiculosAnterior),
                    'trend' => $permisosFuncionamientosVehiculosTrend,
                ],  
                'permisosFuncionamientosPlanos' => [
                    'total' => $permisosFuncionamientosPlanosActual,
                    'growth' => $this->crecimiento($permisosFuncionamientosPlanosActual, $permisosFuncionamientosPlanosAnterior),
                    'trend' => $permisosFuncionamientosPlanosTrend,
                ],  
                'totalPermisosFuncionamientos' => [
                    'total' => $totalPermisosFuncionamientosActual,
                    'growth' => $this->crecimiento($totalPermisosFuncionamientosActual, $totalPermisosFuncionamientosAnterior),
                    'trend' => $totalPermisosFuncionamientosTrend  ?? [],
                ],
                'recaudacionPermisosFuncionamientos' => [
                    'total' => $recaudacionPermisosFuncionamientosActual,
                    'growth' => $this->crecimiento($recaudacionPermisosFuncionamientosActual, $recaudacionPermisosFuncionamientosAnterior),
                    'trend' => $recaudacionPermisosFuncionamientosTrend,
                ],                          
                'recaudacionPermisosFuncionamientosVehiculos' => [
                    'total' => $recaudacionPermisosFuncionamientosVehiculosActual,
                    'growth' => $this->crecimiento($recaudacionPermisosFuncionamientosVehiculosActual, $recaudacionPermisosFuncionamientosVehiculosAnterior),
                    'trend' => $recaudacionPermisosFuncionamientosVehiculosTrend,
                ],
                'recaudacionPermisosFuncionamientosPlanos' => [
                    'total' => $recaudacionPermisosFuncionamientosPlanosActual,
                    'growth' => $this->crecimiento($recaudacionPermisosFuncionamientosPlanosActual, $recaudacionPermisosFuncionamientosPlanosAnterior),
                    'trend' => $recaudacionPermisosFuncionamientosPlanosTrend,
                ],
                'recaudacionTotal' => [
                    'total' => $totalRecaudacionActual,
                    'growth' => $this->crecimiento($totalRecaudacionActual, $totalRecaudacionAnterior),
                    'trend' => $totalRecaudacionTrend ?? [],
                ],
            ],
            'filters' => [
                'rango_fecha' => [
                    'from' => $from->toDateString(),
                    'to' => $to->toDateString(),
                ]
            ]
        ]);
    }

    public function crecimiento($actual, $anterior)
    {
        // 🚫 Ambos en 0
        if ($actual == 0 && $anterior == 0) {
            return 0;
        }

        // 🚀 Antes era 0 y ahora hay valor
        if ($anterior == 0) {
            return 100; // o puedes devolver null si quieres manejarlo distinto
        }

        $growth = (($actual - $anterior) / $anterior) * 100;

        // 🧠 Limitar valores extremos (opcional pero recomendado)
        if ($growth > 999) {
            return 999;
        }

        if ($growth < -999) {
            return -999;
        }

        return round($growth, 1);
    }
}
