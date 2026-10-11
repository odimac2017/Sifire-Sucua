<?php

namespace App\Http\Controllers;

use App\Models\SolicitudInspeccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ValidarSolicitudInspeccionRequest;
use App\Models\ActividadEconomica;
use App\Models\Cliente;
use App\Models\Inspeccion;
use App\Models\Institucion;
use App\Models\Personal;
use App\Models\Plantilla;
use App\Models\ProcesoPermiso;
use App\Models\ProcesoPermisoTarifario;
use App\Models\Tarifario;
use App\Models\TarifarioPeriodo;
use App\Models\TipoInspeccionRequisito;
use App\Models\TipoPermiso;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SolicitudInspeccionController extends Controller
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
        $estado = $request->estado !== 'ALL' ? $request->input('estado', 'GENERADO') : null;          

        $solicitudesInspecciones = SolicitudInspeccion::with([
            'procesoPermiso.cliente',
            'procesoPermiso.actividadEconomica.parroquia',
            'procesoPermiso.tipoPermiso:id,nombre',
        ])
        ->when($search, function ($query) use ($search) {
            $query->whereHas('procesoPermiso.cliente', function ($q) use ($search) {
                $q->where('documento', 'like', "%{$search}%")
                  ->orWhere('nombre', 'like', "%{$search}%");
            });
        })
        ->whereYear('created_at', $year)  
        ->when($estado, function ($query) use ($estado) {
            $query->where('estado', $estado);
        })
        ->orderby('numero')
        ->orderby('created_at')        
        ->paginate(10)
        ->withQueryString();

        return inertia('solicitudesInspecciones/index', [
            'solicitudesInspecciones' => $solicitudesInspecciones,
            'filters' => [
                'search' => $search,
                'year' => $year,
                'estado' => $estado,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {        
        $searchCliente = trim($request->input('searchCliente', ''));

        $cliente = null;

        $tiposPermisos = TipoPermiso::select('id','nombre')
        ->where('estado', 'ACTIVO')
        ->get();

        $tarifarios = Tarifario::with('childrens.tarifarioPeriodoActual')
            ->whereNull('parent_id')
            ->get()
            ->map(function ($parent) {
                return [
                    'label' => $parent->nombre,
                    'options' => $parent->childrens->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'nombre' => $child->nombre . ' ($ ' . (number_format($child->tarifarioPeriodoActual?->valor,2,'.','') ?? '0.00') . ')',
                        ];
                    })->values()
                ];
            });

        $personales = Personal::whereHas('user.roles', function($query) {
            $query->where('rol_id', 4);
        })
        ->where('estado', 'ACTIVO')
        ->get();

        $ultimoNumero = SolicitudInspeccion::whereYear('created_at', now()->year)
        ->max('numero');

        $numero = $ultimoNumero ? $ultimoNumero + 1 : 1;

        if ($searchCliente) {

            $cliente = Cliente::with(['parroquia'])
                ->where('documento', $searchCliente)
                ->first();

            if ($cliente) {

                $actividadesEconomicas = $cliente->actividadesEconomicas()
                ->where('estado', 'ACTIVO')
                ->with('parroquia')
                ->get();

                $cliente->actividades_economicas = $actividadesEconomicas;
            }
        }

        return inertia('solicitudesInspecciones/create', [
            'cliente' => $cliente,
            'tiposPermisos' => $tiposPermisos,
            'tarifarios' => $tarifarios,
            'personales' => $personales,
            'numero' => str_pad($numero,7,'0',STR_PAD_LEFT),
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => (int) $request->input('year', now()->year),
                'estado' => $request->input('estado', 'GENERADO'),
            ],
        ]);
    }

    public function sugerirInspector(Request $request)
    {
        $fecha = $request->fecha;
        $limite = 50;

        $inspectores = Personal::with('user')
        ->select(
            'personal.id',
            'personal.user_id',
            DB::raw('COUNT(solicitudes_inspecciones.id) as total')
        )
        ->leftJoin('solicitudes_inspecciones', function ($join) use ($fecha) {
            $join->on('personal.id', '=', 'solicitudes_inspecciones.personal_id')
                ->whereDate('solicitudes_inspecciones.fecha_inspeccion', $fecha);
        })
        ->groupBy('personal.id', 'personal.user_id')
        ->having('total', '<', $limite)
        ->orderBy('total', 'asc')
        ->get();

        if ($inspectores->isEmpty()) {
            return response()->json(null);
        }

        $min = $inspectores->min('total');

        $inspector = $inspectores
        ->where('total', $min)
        ->random();

        return response()->json([
            'id' => $inspector->id,
            'nombre' => $inspector->nombre,
            'total' => $inspector->total,
            'limite' => $limite
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ValidarSolicitudInspeccionRequest $request)
    {
        // Obtener plantilla
        $plantillaId = Plantilla::where('nombre', 'SOLICITUD DE INSPECCION')
            ->where('predeterminado', 1)
            ->value('id');

        if (!$plantillaId) {
            abort(500, 'No existe plantilla predeterminada para SOLICITUD DE INSPECCION.');
        }

        $tipoInspeccionId = TipoPermiso::where('id', $request->tipo_permiso_id)
            ->value('tipo_inspeccion_id');

        $estado = $tipoInspeccionId === null ? 'REALIZADO' : 'GENERADO';

        $personal = null;

        if ($tipoInspeccionId !== null) {
            $personal = Personal::findOrFail($request->personal_id);
        }
        
        $validated = $request->validated();

        $solicitudInspeccion = null;

        DB::transaction(function () use ($validated, $plantillaId, $personal, $estado, &$solicitudInspeccion, $request) {
            $procesoPermiso = ProcesoPermiso::create([
                'cliente_id' => $validated['cliente_id'],
                'actividad_economica_id' => $validated['actividad_economica_id'],
                'tipo_permiso_id' => $validated['tipo_permiso_id'],
                'artesano_calificado' => $validated['artesano_calificado'],
                'porcentaje_descuento' => $validated['porcentaje_descuento'],
                'valor_impuesto_predial' => $validated['valor_impuesto_predial'],
            ]);

            // Obtener tarifarios seleccionados
            $tarifarios = TarifarioPeriodo::whereIn('tarifario_id', $validated['tarifario_id'])
                ->where('periodo', now()->year)
                ->get(['tarifario_id','valor','valor_fijo']);

            $dataInsert = $tarifarios->map(function ($tarifario) use ($procesoPermiso) {
                return [
                    'proceso_permiso_id' => $procesoPermiso->id,
                    'tarifario_id' => $tarifario->tarifario_id,
                    'valor_tarifario' => $tarifario->valor,
                    'valor_fijo' => $tarifario->valor_fijo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });

            ProcesoPermisoTarifario::insert($dataInsert->toArray());
                
            $solicitudInspeccion = SolicitudInspeccion::create([
                'plantilla_id' => $plantillaId,
                'proceso_permiso_id' => $procesoPermiso->id,
                'personal_id' => $personal ? $validated['personal_id'] : null,
                'nombre_personal' => $personal ? strtoupper($personal->nombre) : null,
                'numero' => $request->numero,
                'fecha_inspeccion' => $validated['fecha_inspeccion'],                
                'observaciones' => $validated['observaciones'],
                'estado' => $estado
            ]);

            // Actualizar fecha_inicio de la actividad económica
            ActividadEconomica::where('id', $validated['actividad_economica_id'])
                ->update([
                    'fecha_inicio' => $validated['fecha_inicio']
                ]);
        });

        return redirect()->route('solicitudesinspecciones.index', [
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
            'estado' => $request->input('estado', 'GENERADO'),
        ])->with('success', 'Solicitud creada correctamente');
    }

    public function generarreinspeccion(Request $request, Inspeccion $inspeccion)
    {
        $procesoPermisoId = $inspeccion->proceso_permiso_id; 
    
        // Obtener plantilla
        $plantillaId = Plantilla::where('nombre', 'SOLICITUD DE INSPECCION')
        ->where('predeterminado', 1)
        ->value('id');

        $ultimoNumero = SolicitudInspeccion::whereYear('created_at', now()->year)
        ->max('numero');

        $numero = $ultimoNumero ? $ultimoNumero + 1 : 1;

        $solicitudInspeccion = null;

        DB::transaction(function () use ($procesoPermisoId, $plantillaId, &$solicitudInspeccion, $inspeccion, $numero) {
            // ✅ Validar que no exista otra solicitud activa
            $existe = SolicitudInspeccion::where('proceso_permiso_id', $procesoPermisoId)
                ->where('estado', 'GENERADO')
                ->where('tipo_solicitud', 'RE')
                ->exists();

            if ($existe) {
                throw new \Exception('Ya existe una solicitud de reinspección generada.');
            }

            $solicitudInspeccion = SolicitudInspeccion::create([
                'plantilla_id' => $plantillaId,
                'proceso_permiso_id' => $procesoPermisoId,
                'personal_id' => $inspeccion->personal_id,
                'nombre_personal' => $inspeccion->nombre_personal,
                'tipo_solicitud' => 'RE',
                'numero' => $numero,
                'fecha_inspeccion' => now(),
                'observaciones' => '',
            ]);

            Inspeccion::where('id', $inspeccion->id)
            ->update(['procesado' => 1]);
        });

        return redirect()->route('inspecciones.novedades', [
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year)
        ])->with('success', 'Solicitud creada correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, SolicitudInspeccion $solicitudInspeccion)
    {
        $solicitudInspeccion->load([
            'procesoPermiso.cliente.parroquia:id,nombre',
            'procesoPermiso.actividadEconomica.parroquia:id,nombre',
            'procesoPermiso.tipoPermiso:id,tipo_inspeccion_id,nombre',
            'procesoPermiso.requisitos:id,proceso_permiso_id,tipo_inspeccion_requisito_id,nombre,ruta,tipo,size',
            'procesoPermiso.requisitos.requisito:id,nombre',
            // tarifario padre
            'procesoPermiso.tarifario.parent:id,nombre',
            // tarifario hijo
            'procesoPermiso.tarifario:id,nombre,parent_id',
        ]);
        
        $tipoInspeccionId = $solicitudInspeccion
        ->procesoPermiso
        ->tipoPermiso
        ->tipo_inspeccion_id;

        $tiposInspeccionesRequisitos = TipoInspeccionRequisito::select('id','nombre')
        ->where('tipo_inspeccion_id', $tipoInspeccionId)
        ->where('estado', 'ACTIVO')
        ->orderBy('nombre')
        ->get();

        return inertia('solicitudesInspecciones/show', [
            'tiposInspeccionesRequisitos' => $tiposInspeccionesRequisitos,
            'solicitudInspeccion' => $solicitudInspeccion,
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => (int) $request->input('year', now()->year),
                'estado' => $request->input('estado', 'GENERADO'),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, SolicitudInspeccion $solicitudInspeccion)
    {
        $tiposPermisos = TipoPermiso::select('id','nombre')
        ->where('estado', 'ACTIVO')
        ->get();

        $tarifarios = Tarifario::with('childrens.tarifarioPeriodoActual')
            ->whereNull('parent_id')
            ->get()
            ->map(function ($parent) {
                return [
                    'label' => $parent->nombre,
                    'options' => $parent->childrens->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'nombre' => $child->nombre . ' ($ ' . (number_format($child->tarifarioPeriodoActual?->valor,2,'.','') ?? '0.00') . ')',
                        ];
                    })->values()
                ];
            });

        $personales = Personal::whereHas('user.roles', function($query) {
            $query->where('rol_id', 4);
        })->get();

        $procesoPermiso = $solicitudInspeccion->procesoPermiso()
            ->with([
                'cliente.parroquia',
                'actividadEconomica.parroquia',
                'tarifarios'
            ])
            ->first();

        return inertia('solicitudesInspecciones/edit', [
            'tiposPermisos' => $tiposPermisos,
            'tarifarios' => $tarifarios,
            'personales' => $personales,
            'procesoPermiso' => $procesoPermiso,
            'solicitudInspeccion' => $solicitudInspeccion,        
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => (int) $request->input('year', now()->year),
                'estado' => $request->input('estado', 'GENERADO'),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ValidarSolicitudInspeccionRequest $request, SolicitudInspeccion $solicitudInspeccion)
    {
        $tipoInspeccionId = TipoPermiso::where('id', $request->tipo_permiso_id)
            ->value('tipo_inspeccion_id');

        $estado = $tipoInspeccionId === null ? 'REALIZADO' : 'GENERADO';

        $personal = null;

        if ($tipoInspeccionId !== null) {
            $personal = Personal::findOrFail($request->personal_id);
        }

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $personal, $estado, $solicitudInspeccion) {
            $solicitudInspeccion->procesoPermiso->update([
                'tipo_permiso_id' => $validated['tipo_permiso_id'],
                'artesano_calificado' => $validated['artesano_calificado'],
                'porcentaje_descuento' => $validated['porcentaje_descuento'],
                'valor_impuesto_predial' => $validated['valor_impuesto_predial'],
            ]);

            $solicitudInspeccion->procesoPermiso->tarifarios()->delete();

            $tarifarios = TarifarioPeriodo::whereIn('tarifario_id', $validated['tarifario_id'])
                ->where('periodo', now()->year)
                ->get(['tarifario_id','valor','valor_fijo']);

            $dataInsert = $tarifarios->map(function ($tarifario) use ($solicitudInspeccion) {
                return [
                    'proceso_permiso_id' => $solicitudInspeccion->procesoPermiso->id,
                    'tarifario_id' => $tarifario->tarifario_id,
                    'valor_tarifario' => $tarifario->valor,
                    'valor_fijo' => $tarifario->valor_fijo,
                ];
            });

            $solicitudInspeccion->procesoPermiso->tarifarios()->createMany($dataInsert->toArray());

            $solicitudInspeccion->update([
                'personal_id' => $personal ? $validated['personal_id'] : null,
                'nombre_personal' => $personal ? strtoupper($personal->nombre) : null,
                'fecha_inspeccion' => $validated['fecha_inspeccion'],
                'observaciones' => $validated['observaciones'],
                'estado' => $estado
            ]);

            // Actualizar fecha_inicio de la actividad económica
            $solicitudInspeccion->procesoPermiso->actividadEconomica->update([
                'fecha_inicio' => $validated['fecha_inicio']
            ]);
        });

        return redirect()->route('solicitudesinspecciones.index', [
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
            'estado' => $request->input('estado', 'GENERADO'),
        ])->with('success', 'Solicitud de inspección actualizada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, SolicitudInspeccion $solicitudInspeccion)
    {
        if ($solicitudInspeccion->estado !== 'GENERADO') {
            return back()->with('error', 'Solo se pueden eliminar solicitudes de inspección en estado GENERADO.');
        }

        DB::transaction(function () use ($solicitudInspeccion) {

            $proceso = $solicitudInspeccion->procesoPermiso;

            $solicitudInspeccion->delete();
    
            if ($proceso) {
                $proceso->delete();
            }
    
        });

       return redirect()->route('solicitudesinspecciones.index', [
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
            'estado' => $request->input('estado', 'GENERADO'),
        ])->with('success', 'Solicitud eliminada correctamente');
    }

    public function descargar($id)
    {
        $solicitudInspeccion = SolicitudInspeccion::findOrFail($id);

        $fecha = Carbon::parse($solicitudInspeccion->created_at);

        if ($solicitudInspeccion->ruta_pdf != null){
            $path = storage_path('app/private/' . $solicitudInspeccion->ruta_pdf);

            if (!file_exists($path)) {
                return back()->with('error', 'Archivo no encontrado');
            }

            return response()->download(
                $path,
                "Solicitud_Inspeccion_{$solicitudInspeccion->numero_formateado}_{$fecha->year}.pdf"
            );
        }else{
            $institucion = Institucion::first();
            $plantilla = Plantilla::find($solicitudInspeccion->plantilla_id);
            $vista = 'pdf.' . $plantilla->vista_blade;
            
            $pdf = Pdf::loadView($vista, compact('institucion','solicitudInspeccion'));

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

            $canvas->page_text(510, 800, "Página {PAGE_NUM} de {PAGE_COUNT}", $fontNormal, 8, [0,0,0]);

            return $pdf->download("Solicitud_{$solicitudInspeccion->numero_formateado}_{$fecha->year}.pdf"); 
        }
    }

    public function preview($id)
    {
        $solicitudInspeccion = SolicitudInspeccion::findOrFail($id);

        $institucion = Institucion::first();
        $plantilla = Plantilla::find($solicitudInspeccion->plantilla_id);
        $vista = 'pdf.' . $plantilla->vista_blade;

        $pdf = Pdf::loadView($vista, compact('institucion','solicitudInspeccion'));

        return $pdf->stream('preview.pdf');
    }
}
