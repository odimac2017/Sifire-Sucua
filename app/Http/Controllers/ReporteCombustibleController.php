<?php

namespace App\Http\Controllers;

use App\Exports\ControlesCombutiblesExport;
use App\Models\ControlCombustible;
use App\Models\Institucion;
use App\Models\Personal;
use App\Models\Plantilla;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReporteCombustibleController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('rango_fecha.from')
            ? Carbon::parse($request->input('rango_fecha.from'))->startOfDay()
            : now()->startOfMonth();

        $to = $request->input('rango_fecha.to')
            ? Carbon::parse($request->input('rango_fecha.to'))->endOfDay()
            : now()->endOfDay();

        $estado = $request->filled('estado') && $request->estado !== 'ALL'
            ? $request->estado
            : null;

        $reportes = ControlCombustible::with([
            'bien',
            'combustible',
        ])
        ->when($estado, function ($query) use ($estado) {
            $query->where('estado', $estado);
        })
        ->whereBetween('fecha', [$from, $to])
        ->orderBy('numero')
        ->orderBy('fecha')
        ->paginate(10)
        ->withQueryString();           

        return inertia('reportes/combustibles', [
            'reportes' => $reportes,
            'filters' => [
                'rango_fecha' => [
                    'from' => $from->toDateString(),
                    'to' => $to->toDateString(),
                ],
                'estado' => $estado ?? 'ALL',
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

        $estado = $request->estado !== 'ALL' ? $request->input('estado', 'GENERADO') : null; 

        $periodo = 'PERIODO DEL ' . $from->translatedFormat('d \d\e F \d\e Y') . ' AL ' . $to->translatedFormat('d \d\e F \d\e Y');

        $institucion = Institucion::first();

        $reportes = ControlCombustible::with([
            'bien',
            'combustible',
        ])
        ->when($estado, function ($query) use ($estado) {
            $query->where('estado', $estado);
        })
        ->whereBetween('fecha', [$from, $to])
        ->orderBy('numero')
        ->orderBy('fecha')
        ->get();

        return Excel::download(new ControlesCombutiblesExport($institucion, $periodo, $reportes), "Reporte_Control_Combustible.xlsx");
    }

    public function exportPdf(Request $request)
    {
        $from = $request->input('rango_fecha.from')
            ? Carbon::parse($request->input('rango_fecha.from'))->startOfDay()
            : now()->startOfMonth();

        $to = $request->input('rango_fecha.to')
            ? Carbon::parse($request->input('rango_fecha.to'))->endOfDay()
            : now()->endOfDay();

        $estado = $request->estado !== 'ALL' ? $request->input('estado', 'GENERADO') : null; 

        $periodo = 'PERIODO DEL ' . $from->translatedFormat('d \d\e F \d\e Y') . ' AL ' . $to->translatedFormat('d \d\e F \d\e Y');

        $institucion = Institucion::first();

        $reportes = ControlCombustible::with([
            'bien',
            'combustible',
        ])
        ->when($estado, function ($query) use ($estado) {
            $query->where('estado', $estado);
        })
        ->whereBetween('fecha', [$from, $to])
        ->orderBy('numero')
        ->orderBy('fecha')
        ->get();

        $personal = Personal::with('personalPuestos')->findOrFail(Auth::user()->personal?->id);

        $cargoPredeterminado = $personal->personalPuestos
            ->firstWhere('predeterminado', 1);

        $personal_firma = [
            (object)[
                'abreviatura' => $personal->titulo?->abreviatura ?? '',
                'nombrePersonal' => $personal->nombre, // Accessor ya definido
                'nombrePuesto' => $cargoPredeterminado?->puesto?->nombre ?? '',
            ]
        ];

        // Obtener plantilla
        $plantilla = Plantilla::where('nombre', 'REPORTE CONTROL COMBUSTIBLE')
        ->where('predeterminado', 1)
        ->first();

        // 📄 Vista dinámica
        $vista = 'pdf.' . $plantilla->vista_blade;

        $pdf = Pdf::loadView($vista, compact('institucion','periodo','reportes','personal_firma'));

        // 📏 Configurar tamaño
        if ($plantilla->tamano_papel != 'TAMAÑO PERSONAL') {            
            $pdf->setPaper($plantilla->tamano_papel, $plantilla->orientacion_papel ?? 'portrait');
        } else {
            $pdf->setPaper([
                0,
                0,
                $plantilla->ancho,
                $plantilla->alto
            ], $plantilla->orientacion_papel ?? 'portrait');
        }

        $fechaHora = \Carbon\Carbon::now()->translatedFormat('d \d\e F \d\e Y H:i');

        $pdf->render();

        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();

        $fontNormal = $fontMetrics->getFont("Roboto", "normal");
        $fontItalic = $fontMetrics->getFont("Roboto", "italic");

        $canvas->page_text(35, 560, "Generado el $fechaHora", $fontItalic, 8, [0,0,0]);
        $canvas->page_text(760, 560, "Página {PAGE_NUM} de {PAGE_COUNT}", $fontNormal, 8, [0,0,0]);

        return $pdf->download("Reporte_Control_Combustible.pdf");
    }
}
