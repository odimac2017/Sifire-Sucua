<?php

namespace App\Http\Controllers;

use App\Models\ParteEmergencia;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EstadisticaEmergenciaController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('rango_fecha.from')
            ? Carbon::parse($request->input('rango_fecha.from'))->startOfDay()
            : now()->startOfMonth();

        $to = $request->input('rango_fecha.to')
            ? Carbon::parse($request->input('rango_fecha.to'))->endOfDay()
            : now()->endOfDay();

        $emergencias = ParteEmergencia::whereBetween('fecha_llamada', [$from, $to])
        ->selectRaw("
            DATE_FORMAT(fecha_llamada, '%Y-%m') as mes,
            COUNT(id) as total
        ")
        ->groupBy('mes')
        ->orderBy('mes')
        ->get();

        $emergenciasCategoria = ParteEmergencia::query()
            ->join(
                'parte_emergencia_categorias',
                'partes_emergencias.id',
                '=',
                'parte_emergencia_categorias.parte_emergencia_id'
            )
            ->join(
                'categorias_emergencias',
                'parte_emergencia_categorias.categoria_emergencia_id',
                '=',
                'categorias_emergencias.id'
            )
            ->join(
                'categorias_emergencias as parent',
                'categorias_emergencias.parent_id',
                '=',
                'parent.id'
            )
            ->whereBetween('partes_emergencias.fecha_llamada', [$from, $to])
            ->selectRaw('
                parent.nombre as categoria,
                COUNT(*) as total
            ')
            ->groupBy('parent.nombre')
            ->orderByDesc('total')
            ->get();

        $emergenciasParroquia = ParteEmergencia::query()
        ->join('parroquias', 'partes_emergencias.parroquia_id', '=', 'parroquias.id')
        ->whereBetween('partes_emergencias.fecha_llamada', [$from, $to])
        ->selectRaw("
            parroquias.nombre as parroquia,
            COUNT(partes_emergencias.id) as total
        ")
        ->groupBy('parroquias.nombre')
        ->orderByDesc('total')
        ->get();

        return inertia('estadisticas/emergencias', [
            'emergencias' => $emergencias,
            'emergenciasCategoria' => $emergenciasCategoria,
            'emergenciasParroquia' => $emergenciasParroquia,
            'filters' => [
                'rango_fecha' => [
                    'from' => $from->toDateString(),
                    'to' => $to->toDateString(),
                ]
            ]
        ]);
    }    
}
