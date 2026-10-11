<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidarInformeDiarioRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\InformeDiario;
use App\Models\Inspeccion;
use App\Models\Institucion;
use App\Models\Notificacion;
use App\Models\Numeracion;
use App\Models\ParteConsigna;
use App\Models\PersonalPuesto;
use App\Models\Plantilla;
use App\Models\SolicitudInspeccion;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class InformeDiarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $year = $request->filled('year') && is_numeric($request->year)
            ? (int) $request->year
            : now()->year;    

        $informesDiarios = InformeDiario::when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%");
            });
        })
            ->whereYear('fecha_informe', $year)
            ->orderByDesc('fecha_informe')
            ->paginate(10)
            ->withQueryString();
        
        return inertia('informesDiarios/index', [
            'informesDiarios' => $informesDiarios,
            'auth_personal_id' => Auth::user()->personal?->id ?? null,
            'filters' => [
                'search' => $search,
                'year' => $year,
            ],
        ]);
    }

    public function create(Request $request)
    {   
        $personalesPuestos = PersonalPuesto::with(['personal.titulo', 'puesto'])
            ->where('personal_id', '!=', Auth::user()->personal->id)
            ->whereHas('personal', function ($q) {
                $q->where('estado', 'ACTIVO');
            })
            ->where('predeterminado', 1)
            ->join('personal', 'personal.id', 'personal_puestos.personal_id')
            ->orderBy('personal.apellidos')
            ->orderBy('personal.nombres')
            ->select('personal_puestos.*')
            ->get()
            ->map(function ($pp) {
                return [
                    'id' => $pp->id,
                    'nombre' => strtoupper(trim("{$pp->personal->nombre}, {$pp->puesto->nombre}")),
                ];
            });
        
        // 🔢 Obtener el último número del día (opcional: por fecha o global)
        $ultimaSecuencia = InformeDiario::whereYear('fecha_informe', now()->year)
            ->max('secuencia');

        $secuencia = $ultimaSecuencia ? $ultimaSecuencia + 1 : 1;

        $formato = Numeracion::where('nombre', 'INFORME DIARIO')
            ->value('formato');

        // 🧾 Construir número
        $numero = $formato . '-' . str_pad($secuencia, 7, '0', STR_PAD_LEFT) . '-' . now()->year;

        return inertia('informesDiarios/create', [
            'personalesPuestos' => $personalesPuestos,
            'numero' => $numero,
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => (int) $request->input('year', now()->year),
            ],
        ]);
    }

    public function store(ValidarInformeDiarioRequest $request)
    {
        $plantillaId = Plantilla::where('nombre', 'INFORME DIARIO')
            ->where('predeterminado', 1)
            ->value('id');

        $personalPuesto = PersonalPuesto::with(['personal.titulo','puesto'])
            ->where('personal_id', Auth::user()->personal->id)
            ->where('predeterminado', 1)
            ->firstOrFail();

        $personalPuestoDirige = PersonalPuesto::with(['personal.titulo','puesto'])
            ->findOrFail($request->personal_puesto_dirige_id);

        // 🔢 Obtener el último número del día (opcional: por fecha o global)
        $ultimaSecuencia = InformeDiario::whereYear('fecha_informe', now()->year)
            ->max('secuencia');

        $secuencia = $ultimaSecuencia ? $ultimaSecuencia + 1 : 1;

        $formato = Numeracion::where('nombre', 'INFORME DIARIO')
            ->value('formato');

        // 🧾 Construir número
        $numero = $formato . '-' . str_pad($secuencia, 7, '0', STR_PAD_LEFT) . '-' . now()->year;
      
        $validated = $request->validated();

        $validated['plantilla_id'] = $plantillaId;
        $validated['personal_id'] = Auth::user()->personal->id;
        $validated['personal_puesto_id'] = $personalPuesto->id;
        $validated['nombre_personal'] = strtoupper($personalPuesto->personal->nombre);
        $validated['puesto_personal'] = strtoupper($personalPuesto->puesto->nombre);
        $validated['nombre_dirige'] = strtoupper($personalPuestoDirige->personal->nombre);
        $validated['puesto_dirige'] = strtoupper($personalPuestoDirige->puesto->nombre);
        $validated['numero'] = $numero;
        $validated['secuencia'] = $secuencia;
        
        InformeDiario::create($validated);

        return redirect()->route('informesdiarios.index', [
           'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
        ])->with('success', 'Informe diario creado correctamente');
    }    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, InformeDiario $informeDiario)
    {
        // 👇 VALIDACIÓN DE SEGURIDAD
        if ($informeDiario->personal_id != Auth::user()->personal->id) {
            abort(403, 'No autrizado para editar este informe diario.');
        }        

        $personalesPuestos = PersonalPuesto::with(['personal.titulo', 'puesto'])
            ->where('personal_id', '!=', Auth::user()->personal->id)
            ->whereHas('personal', function ($q) {
                $q->where('estado', 'ACTIVO');
            })
            ->where('predeterminado', 1)
            ->join('personal', 'personal.id', 'personal_puestos.personal_id')
            ->orderBy('personal.apellidos')
            ->orderBy('personal.nombres')
            ->select('personal_puestos.*')
            ->get()
            ->map(function ($pp) {
                return [
                    'id' => $pp->id,
                    'nombre' => strtoupper(trim("{$pp->personal->nombre}, {$pp->puesto->nombre}")),
                ];
            });

        return inertia('informesDiarios/edit', [
            'personalesPuestos' => $personalesPuestos,
            'informeDiario' => $informeDiario,
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => (int) $request->input('year', now()->year),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ValidarInformeDiarioRequest $request, InformeDiario $informeDiario)
    {       
        $personalPuesto = PersonalPuesto::with(['personal.titulo','puesto'])
            ->where('personal_id', Auth::user()->personal->id)
            ->where('predeterminado', 1)
            ->firstOrFail(); 

        $personalPuestoDirige = PersonalPuesto::with(['personal.titulo','puesto'])
            ->findOrFail($request->personal_puesto_dirige_id);

        $firmantes = $informeDiario->firmantes()->count();

        $validated = $request->validated();        

        // Agregar campos automáticos  
        $validated['nombre_personal'] = strtoupper($personalPuesto->personal->nombre);
        $validated['puesto_personal'] = strtoupper($personalPuesto->puesto->nombre);
        $validated['nombre_dirige'] = strtoupper($personalPuestoDirige->personal->nombre);
        $validated['puesto_dirige'] = strtoupper($personalPuestoDirige->puesto->nombre);
        $validated['estado_firma'] = $firmantes > 0 ? 'PENDIENTE' : 'SIN_FIRMA';
        
        $informeDiario->update($validated);

        return redirect()->route('informesdiarios.index', [
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
        ])->with('success', 'Informe diario actualizado correctamente');       
    }

    public function obtenerTotales(Request $request)
    {   
        $fecha = $request->fecha;

        $inspeccionesAprobadas = Inspeccion::whereDate('fecha_inspeccion', $fecha)
            ->where('estado', 'APROBADO')
            ->where('personal_id', Auth::user()->personal?->id)
            ->whereHas('procesoPermiso', function ($query) {
                $query->where('tipo_permiso_id', 1);
            })
            ->count();
        
        $inspeccionesReinspeccion = Inspeccion::whereDate('fecha_inspeccion', $fecha)
            ->where('estado', 'REINSPECCION')
            ->where('personal_id', Auth::user()->personal?->id)
            ->whereHas('procesoPermiso', function ($query) {
                $query->where('tipo_permiso_id', 1);
            })
            ->count();
        
        $inspeccionesNoRealizadas = SolicitudInspeccion::whereDate('fecha_inspeccion', $fecha)
            ->where('estado', 'GENERADO')
            ->where('personal_id', Auth::user()->personal?->id)
            ->count();
        
        $inspeccionesVehiculares = Inspeccion::whereDate('fecha_inspeccion', $fecha)
            ->where('estado', 'APROBADO')
            ->where('personal_id', Auth::user()->personal?->id)
            ->whereHas('procesoPermiso', function ($query) {
                $query->where('tipo_permiso_id', 2);
            })
            ->count();
        
        $notificacionesRealizadas = Notificacion::whereDate('fecha_entrega', $fecha)
            ->where('estado', 'ENTREGADO')
            ->where('personal_id', Auth::user()->personal?->id)
            ->count();
        
        $totales = [
            'inspecciones_aprobadas' => $inspeccionesAprobadas,
            'inspecciones_reinspeccion' => $inspeccionesReinspeccion,
            'inspecciones_no_realizadas' => $inspeccionesNoRealizadas,
            'inspecciones_vehiculares' => $inspeccionesVehiculares,
            'notificaciones_realizadas' => $notificacionesRealizadas,
            'total_general' => $inspeccionesAprobadas + $inspeccionesReinspeccion + $inspeccionesVehiculares + $inspeccionesNoRealizadas + $notificacionesRealizadas,
        ];

        $inspeccionesAprobadasTexto = Inspeccion::with([
                'procesoPermiso.cliente',
                'procesoPermiso.actividadEconomica'
            ])
            ->whereDate('fecha_inspeccion', $fecha)
            ->where('estado', 'APROBADO')
            ->where('personal_id', Auth::user()->personal?->id)
            ->whereHas('procesoPermiso', function ($query) {
                $query->where('tipo_permiso_id', 1);
            })
            ->get();

        $observaciones = '';
        
        if ($inspeccionesAprobadas > 0){

            $observaciones .= '<p><strong>Inspecciones Aprobadas:</strong></p><ul>';

            foreach ($inspeccionesAprobadasTexto as $inspeccion) {
                $cliente = $inspeccion->procesoPermiso?->cliente?->nombre ?? 'Sin cliente';
                $actividad = $inspeccion->procesoPermiso?->actividadEconomica?->e_nombre_comercial ?? 'Sin actividad';

                $observaciones .= '<li>El establecimiento ' . $actividad . ' perteneciente a ' . $cliente . ', cumple con las medidas de seguridad.' . '</li>';
            }

            $observaciones .= '</ul>';
        }

        $inspeccionesReinspeccionTexto = Inspeccion::whereDate('fecha_inspeccion', $fecha)
            ->where('estado', 'REINSPECCION')
            ->where('personal_id', Auth::user()->personal?->id)
            ->whereHas('procesoPermiso', function ($query) {
                $query->where('tipo_permiso_id', 1);
            })
            ->get();
        
        if ($inspeccionesReinspeccion > 0){

            $observaciones .= '<p><strong>Locales sujetos a reinspección:</strong></p><ul>';

            foreach ($inspeccionesReinspeccionTexto as $inspeccion) {
                $cliente = $inspeccion->procesoPermiso?->cliente?->nombre ?? 'Sin cliente';
                $actividad = $inspeccion->procesoPermiso?->actividadEconomica?->e_nombre_comercial ?? 'Sin actividad';

                $observaciones .= '<li>El establecimiento ' . $actividad . ' perteneciente a ' . $cliente . ', queda sujeto a reinspección por no cumplir con las medidas de seguridad. Se emiten las siguiente medidas obligatorias: ' . $inspeccion->medidas_obligatorias . '.</li>';
            }

            $observaciones .= '</ul>';
        }

        return response()->json([
            'totales' => $totales,
            'observaciones' => $observaciones,
        ]);
    }    

    public function descargar($id)
    {
        $informeDiario = InformeDiario::findOrFail($id);

        if ($informeDiario->ruta_pdf_firmado != null){
            $path = storage_path('app/private/' . $informeDiario->ruta_pdf_firmado);

            if (!file_exists($path)) {
                return back()->with('error', 'Archivo no encontrado');
            }

            return response()->download(
                $path,
                "Informe_Diario_{$informeDiario->numero}.pdf"
            );
        }else{         
            $institucion = Institucion::first();
            $plantilla = Plantilla::find($informeDiario->plantilla_id);
            $vista = 'pdf.' . $plantilla->vista_blade;

            $pdf = Pdf::loadView($vista, compact('institucion','informeDiario'));

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

            $pdf->render();

            $dompdf = $pdf->getDomPDF();
            $canvas = $dompdf->getCanvas();
            $fontMetrics = $dompdf->getFontMetrics();

            $fontNormal = $fontMetrics->getFont("Roboto", "normal");

            $canvas->page_text(30, 810, "Página {PAGE_NUM} de {PAGE_COUNT}", $fontNormal, 8, [0,0,0]);

            return $pdf->stream("Informe_Diario_{$informeDiario->numero}.pdf");
        }
    }    
}
