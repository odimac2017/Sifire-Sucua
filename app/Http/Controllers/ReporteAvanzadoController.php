<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use App\Models\PermisoFuncionamiento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PermisosFuncionamientosExport;

class ReporteAvanzadoController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('rango_fecha.from')
            ? Carbon::parse($request->input('rango_fecha.from'))->startOfDay()
            : now()->startOfMonth();

        $to = $request->input('rango_fecha.to')
            ? Carbon::parse($request->input('rango_fecha.to'))->endOfDay()
            : now()->endOfDay();      
               
        $reportes = PermisoFuncionamiento::with([
            'procesoPermiso.cliente',
            'procesoPermiso.actividadEconomica',
            'procesoPermiso.tipoPermiso'
        ])
        ->select('*')
        ->selectRaw("'P' as tabla")
        ->whereBetween('fecha_expedicion', [$from, $to])
        ->orderBy('numero')
        ->paginate(10)
        ->withQueryString();

        $totales = PermisoFuncionamiento::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN estado = 'EXPEDIDO' THEN 1 ELSE 0 END) as expedidos,
            SUM(CASE WHEN estado = 'ANULADO' THEN 1 ELSE 0 END) as anulados,

            SUM(CASE WHEN estado = 'EXPEDIDO' THEN valor_total ELSE 0 END) as monto_expedidos,
            SUM(CASE WHEN estado = 'ANULADO' THEN valor_total ELSE 0 END) as monto_anulados,

            SUM(valor_total) as monto_total
        ")
        ->whereBetween('fecha_expedicion', [$from, $to])
        ->first();  
       
        return inertia('reportes/avanzados', [
            'reportes' => $reportes,
            'totales' => $totales,        
            'filters' => [
                'rango_fecha' => [
                    'from' => $from->toDateString(),
                    'to' => $to->toDateString(),
                ]
            ]
        ]);
    }

    public function exportExcel(Request $request)
    {
        $from = $request->input('rango_fecha.from')
            ? Carbon::parse($request->input('rango_fecha.from'))->startOfDay()
            : now()->startOfMonth();

        $to = $request->input('rango_fecha.to')
            ? Carbon::parse($request->input('rango_fecha.to'))->endOfDay()
            : now()->endOfDay();       
        
        $institucion = Institucion::first();

        $reporte = (object) [
            'numero' => null,
            'fecha' => 'PERIODO DEL ' . $from->translatedFormat('d \d\e F \d\e Y') . ' AL ' . $to->translatedFormat('d \d\e F \d\e Y')
        ];
        
        $permisos = PermisoFuncionamiento::with([
            'procesoPermiso.cliente',
            'procesoPermiso.actividadEconomica',
            'procesoPermiso.tipoPermiso'
        ])
        ->whereBetween('fecha_expedicion', [$from, $to])
        ->orderBy('numero')
        ->orderBy('fecha_expedicion')
        ->orderBy('numero')
        ->get();
        
        return Excel::download(new PermisosFuncionamientosExport($institucion, $reporte, $permisos), "Reporte_Permisos.xlsx");       
    }
}