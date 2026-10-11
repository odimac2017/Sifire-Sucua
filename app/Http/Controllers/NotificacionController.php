<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidarBoletaNotificacionRequest;
use App\Http\Requests\ValidarNotificacionProcesoRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\ValidarNotificacionRequest;
use App\Models\ActividadEconomica;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Personal;
use App\Models\Plantilla;
use Illuminate\Support\Facades\Auth;
use App\Models\Institucion;
use App\Models\Notificacion;
use App\Models\ProcesoPermiso;
use App\Models\ProcesoPermisoTarifario;
use App\Models\SolicitudInspeccion;
use App\Models\Tarifario;
use App\Models\TarifarioPeriodo;
use App\Models\TipoPermiso;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class NotificacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $year = $request->integer('year') ?: now()->year;
        $estado = $request->estado !== 'ALL' ? $request->input('estado', 'ALL') : null;

        $procesosPermisos = ProcesoPermiso::with([
            'cliente',
            'actividadEconomica.parroquia',
            'tipoPermiso:id,nombre',
            'inspeccionAprobada',
            'notificaciones',
        ])
        ->leftJoin('inspecciones as ia', function ($join) {
            $join->on('ia.proceso_permiso_id', '=', 'procesos_permisos.id')
                ->where('ia.estado', 'APROBADO');
        })
        ->when($search, function ($query) use ($search) {
            $query->whereHas('cliente', function ($q) use ($search) {
                $q->where('documento', 'like', "%{$search}%")
                  ->orWhere('nombre', 'like', "%{$search}%");
            });
        })
        ->when($estado && $estado !== 'ALL', function ($query) use ($estado) {
            $query->whereHas('notificaciones', fn ($q) => $q->where('estado', $estado));
        })
        ->whereNull('finished_at')
        ->where(function ($query) use ($year) {

            // Inspecciones vencidas
            $query->whereHas('inspeccionAprobada', function ($q) use ($year) {
                $q->whereYear('fecha_inspeccion', $year)
                ->whereDate('plazo_permiso', '<', today());
            });

            // Procesos creados desde notificaciones
            $query->orWhere(function ($q) use ($year) {
                $q->whereYear('procesos_permisos.created_at', $year)
                ->has('notificaciones');
            });

        })
        ->orderBy('ia.fecha_inspeccion')
        ->orderBy('ia.numero')
        ->select('procesos_permisos.*')
        ->withCount('notificaciones')
        ->paginate(10)
        ->withQueryString();
        
        $roles = Auth::user()->roles->pluck('id');

        return inertia('notificaciones/index', [
            'procesosPermisos' => $procesosPermisos,
            'auth_personal_id' => Auth::user()->personal?->id ?? null,
            'administrador' => $roles->contains(1),
            'supervisor' => $roles->contains(3),
            'inspector' => $roles->contains(4),
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
    public function create(Request $request, ProcesoPermiso $procesoPermiso)
    {
        // VALIDACIÓN DE SEGURIDAD
        if (!Auth::user()->roles->pluck('id')->intersect([1, 3])->isNotEmpty()) {
            abort(403, 'No autorizado para crear notificaciones.');
        }

        $procesoPermiso->load([
            'cliente.parroquia',
            'actividadEconomica.parroquia',
        ])->loadCount('notificaciones');

        $personales = Personal::whereHas('user.roles', function($query) {
            $query->where('rol_id', 4);
        })->get();

        $ultimoNumero = Notificacion::whereYear('created_at', now()->year)
        ->max('numero');

        $numero = $ultimoNumero ? $ultimoNumero + 1 : 1;

        return inertia('notificaciones/create', [
            'procesoPermiso' => $procesoPermiso,
            'personales' => $personales,
            'numero' => str_pad($numero,7,'0',STR_PAD_LEFT),
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => $request->input('year', now()->year),
                'estado' => $request->estado !== 'ALL' ? $request->input('estado', 'ALL') : null,
            ],
        ]);
    }

    public function createProcess(Request $request)
    {
        // VALIDACIÓN DE SEGURIDAD
        if (!Auth::user()->roles->pluck('id')->intersect([1, 3])->isNotEmpty()) {
            abort(403, 'No autorizado para crear notificaciones.');
        }

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

        $ultimoNumeroSolicitud = Notificacion::whereYear('created_at', now()->year)
        ->max('numero');

        $numeroSolicitud = $ultimoNumeroSolicitud ? $ultimoNumeroSolicitud + 1 : 1;

        $ultimoNumeroBoleta = Notificacion::whereYear('created_at', now()->year)
        ->max('numero');

        $numeroBoleta = $ultimoNumeroBoleta ? $ultimoNumeroBoleta + 1 : 1;

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

        return inertia('notificaciones/createProcess', [
            'cliente' => $cliente,
            'tiposPermisos' => $tiposPermisos,
            'tarifarios' => $tarifarios,
            'personales' => $personales,
            'numeroSolicitud' => str_pad($numeroSolicitud,7,'0',STR_PAD_LEFT),
            'numeroBoleta' => str_pad($numeroBoleta,7,'0',STR_PAD_LEFT),
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => $request->input('year', now()->year),
                'estado' => $request->estado !== 'ALL' ? $request->input('estado', 'ALL') : null,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ValidarNotificacionRequest $request)
    {
        $validated = $request->validated();
        
        // Obtener plantilla
        $plantillaId = Plantilla::where('nombre', 'BOLETA DE NOTIFICACION')
        ->where('predeterminado', 1)
        ->value('id');

        if (!$plantillaId) {
            abort(500, 'No existe plantilla predeterminada para BOLETA DE NOTIFICACION.');
        }
        
        // Obtener inspector
        $personal = Personal::findOrFail($validated['personal_id']);
        
        // Calcular número de notificación (seguridad)
        $numeroNotificacion = Notificacion::where(
            'proceso_permiso_id',
            $validated['proceso_permiso_id']
        )->count() + 1;

        if ($numeroNotificacion > 3) {
            abort(422, 'Este proceso ya tiene el máximo de notificaciones.');
        }       
        
        Notificacion::create([
            'plantilla_id' => $plantillaId,
            'proceso_permiso_id' => $validated['proceso_permiso_id'],
            'personal_id' => $validated['personal_id'],
            'nombre_personal' => strtoupper($personal->nombre),
            'numero' => $request->numero,
            'numero_notificacion' => $numeroNotificacion,
            'documento_representante_legal' => $validated['documento_representante_legal'],
            'nombre_representante_legal' => $validated['nombre_representante_legal'],
            'observaciones' => $validated['observaciones'],
        ]);

        return redirect()->route('notificaciones.index', [
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
            'estado' => $request->estado !== 'ALL' ? $request->input('estado', 'ALL') : null,
        ])->with('success', 'Notificación creada correctamente');
    }

    public function storeProcess(ValidarNotificacionProcesoRequest $request)
    {
        // Obtener plantilla
        $plantillaSolicitudId = Plantilla::where('nombre', 'SOLICITUD DE INSPECCION')
            ->where('predeterminado', 1)
            ->value('id');

        if (!$plantillaSolicitudId) {
            abort(500, 'No existe plantilla predeterminada para SOLICITUD DE INSPECCION.');
        }

        $plantillaBoletaId = Plantilla::where('nombre', 'BOLETA DE NOTIFICACION')
        ->where('predeterminado', 1)
        ->value('id');

        if (!$plantillaBoletaId) {
            abort(500, 'No existe plantilla predeterminada para BOLETA DE NOTIFICACION.');
        }

        $ultimoNumeroSolicitud = SolicitudInspeccion::whereYear('created_at', now()->year)
        ->max('numero');

        $numeroSolicitud = $ultimoNumeroSolicitud ? $ultimoNumeroSolicitud + 1 : 1;

        $ultimoNumeroBoleta = Notificacion::whereYear('created_at', now()->year)
        ->max('numero');

        $numeroBoleta = $ultimoNumeroBoleta ? $ultimoNumeroBoleta + 1 : 1;

        $personal = Personal::findOrFail($request->personal_id);

        $dias = Configuracion::value('plazo_primera_notificacion');        
        
        $fecha_plazo = Carbon::now()->addWeekdays($dias);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $plantillaSolicitudId, $plantillaBoletaId, $numeroSolicitud, $numeroBoleta, $personal, $fecha_plazo, $request) {
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
                
            SolicitudInspeccion::create([
                'plantilla_id' => $plantillaSolicitudId,
                'proceso_permiso_id' => $procesoPermiso->id,
                'personal_id' => $validated['personal_id'],
                'nombre_personal' => strtoupper($personal->nombre),
                'numero' => $numeroSolicitud,
                'fecha_inspeccion' => $validated['fecha_inspeccion'],                
                'observaciones' => $validated['observaciones']
            ]);

            Notificacion::create([
                'plantilla_id' => $plantillaBoletaId,
                'proceso_permiso_id' => $procesoPermiso->id,
                'personal_id' => $validated['personal_id'],
                'nombre_personal' => strtoupper($personal->nombre),
                'numero' => $numeroBoleta,
                'numero_notificacion' => $request->numero_notificacion,
                'documento_representante_legal' => $validated['documento_representante_legal'],
                'nombre_representante_legal' => $validated['nombre_representante_legal'],
                'documento_persona_recibe' => $validated['documento_persona_recibe'],
                'nombre_persona_recibe' => $validated['nombre_persona_recibe'],
                'fecha_entrega' => now(),
                'fecha_plazo' => $fecha_plazo,
                'observaciones' => $validated['observaciones'],
                'estado' => 'ENTREGADO'
            ]);

            // Actualizar fecha_inicio de la actividad económica
            ActividadEconomica::where('id', $validated['actividad_economica_id'])
                ->update([
                    'fecha_inicio' => $validated['fecha_inicio']
                ]);
        });

        return redirect()->route('notificaciones.index', [
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
            'estado' => $request->estado !== 'ALL' ? $request->input('estado', 'ALL') : null,
        ])->with('success', 'Proceso de notificación creado correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Notificacion $notificacion)
    {        
        $notificacion->load([
            'procesoPermiso.cliente.parroquia',
            'procesoPermiso.actividadEconomica.parroquia',
            'archivos:id,notificacion_id,nombre,ruta,tipo,size'
        ]);  

        return inertia('notificaciones/show', [
            'notificacion' => $notificacion,
            'auth_personal_id' => Auth::user()->personal?->id ?? null,
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => (int) $request->input('year', now()->year),
                'estado' => $request->estado !== 'ALL' ? $request->input('estado', 'ALL') : null,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Notificacion $notificacion)
    {
        // VALIDACIÓN DE SEGURIDAD
        if (!Auth::user()->roles->pluck('id')->intersect([1, 3])->isNotEmpty()) {
            abort(403, 'No autorizado para editar notificaciones.');
        }

        $procesoPermiso = $notificacion->procesoPermiso()
        ->with([
            'cliente.parroquia',
            'actividadEconomica.parroquia',
        ])
        ->first();

        $personales = Personal::whereHas('user.roles', function($query) {
            $query->where('rol_id', 4);
        })->get();

        return inertia('notificaciones/edit', [
            'procesoPermiso' => $procesoPermiso,
            'notificacion' => $notificacion,
            'personales' => $personales,      
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => (int) $request->input('year', now()->year),
                'estado' => $request->estado !== 'ALL' ? $request->input('estado', 'ALL') : null,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ValidarNotificacionRequest $request, Notificacion $notificacion)
    {
        $validated = $request->validated();    

        // Obtener inspector
        $personal = Personal::findOrFail($validated['personal_id']);
        
        $notificacion->update([
            'personal_id' => $validated['personal_id'],
            'nombre_personal' => strtoupper($personal->nombre),
            'documento_representante_legal' => $validated['documento_representante_legal'],
            'nombre_representante_legal' => $validated['nombre_representante_legal'],
            'observaciones' => $validated['observaciones'],
        ]);

        return redirect()->route('notificaciones.index', [
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
            'estado' => $request->input('estado', 'PROCESO'),
        ])->with('success', 'Notificación editada correctamente');        
    }

    public function entregar(Request $request, Notificacion $notificacion)
    {
        // VALIDACIÓN DE SEGURIDAD
        if (!Auth::user()->roles->contains('id', 4)) {
            abort(403, 'No autorizado para entregar boletas de notificación.');
        }

        $procesoPermiso = $notificacion->procesoPermiso()
        ->with([
            'cliente.parroquia',
            'actividadEconomica.parroquia',
        ])
        ->first();

        return inertia('notificaciones/boleta', [
            'procesoPermiso' => $procesoPermiso,
            'notificacion' => $notificacion, 
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => (int) $request->input('year', now()->year),
                'estado' => $request->estado !== 'ALL' ? $request->input('estado', 'ALL') : null,
            ],
        ]);
    }

    public function boleta(ValidarBoletaNotificacionRequest $request, Notificacion $notificacion)
    {
        $validated = $request->validated();    

        // Determinar plazo según número de notificación
        switch ($request->numero_notificacion) {
            case 1:
                $dias = Configuracion::value('plazo_primera_notificacion');
                break;
            case 2:
                $dias = Configuracion::value('plazo_segunda_notificacion');
                break;
            default:
                $dias = Configuracion::value('plazo_tercera_notificacion');
                break;
        }
        
        $fecha_plazo = Carbon::now()->addWeekdays($dias);

        $notificacion->update([            
            'documento_representante_legal' => $validated['documento_representante_legal'],
            'nombre_representante_legal' => $validated['nombre_representante_legal'],
            'documento_persona_recibe' => $validated['documento_persona_recibe'],
            'nombre_persona_recibe' => $validated['nombre_persona_recibe'],
            'fecha_entrega' => now(),
            'fecha_plazo' => $fecha_plazo,
            'observaciones' => $validated['observaciones'],
            'estado' => 'ENTREGADO'
        ]);

        return redirect()->route('notificaciones.index', [
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
            'estado' => $request->estado !== 'ALL' ? $request->input('estado', 'ALL') : null,
        ])->with('success', 'Boleta entregada correctamente');        
    }

    public function descargarComprobante($id)
    {
        $notificacion = Notificacion::findOrFail($id);

        $fecha = Carbon::parse($notificacion->fecha_entrega);
        
        $configuracion = Configuracion::first();
        $institucion = Institucion::first();

        $plantilla = Plantilla::where('nombre', 'COMPROBANTE DE NOTIFICACION A ESTABLECIMIENTOS')
            ->where('predeterminado', 1)
            ->first();

        $vista = 'pdf.' . $plantilla->vista_blade;
        
        $pdf = Pdf::loadView($vista, compact('institucion','configuracion','notificacion'));

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

        return $pdf->download("Comprobante_Notificacion_{$notificacion->numero_formateado}_{$fecha->year}.pdf"); 
    }

    public function descargar($id)
    {
        $notificacion = Notificacion::findOrFail($id);

        $fecha = Carbon::parse($notificacion->fecha_entrega);

        if ($notificacion->ruta_pdf != null){
            $path = storage_path('app/private/' . $notificacion->ruta_pdf);

            if (!file_exists($path)) {
                return back()->with('error', 'Archivo no encontrado');
            }

            return response()->download(
                $path,
                "Boleta_Notificacion_{$notificacion->numero_formateado}_{$fecha->year}_firmado.pdf"
            );
        }else{
            $configuracion = Configuracion::first();
            $institucion = Institucion::first();
            $plantilla = Plantilla::find($notificacion->plantilla_id);
            $vista = 'pdf.' . $plantilla->vista_blade;
            
            $pdf = Pdf::loadView($vista, compact('institucion','configuracion','notificacion'));

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
           
            return $pdf->download("Boleta_Notificacion_{$notificacion->numero_formateado}_{$fecha->year}.pdf");
        }
    }

    public function preview($id)
    {
        $notificacion = Notificacion::findOrFail($id);

        $institucion = Institucion::first();
        $plantilla = Plantilla::find($notificacion->plantilla_id);
        $vista = 'pdf.' . $plantilla->vista_blade;

        $pdf = Pdf::loadView($vista, compact('institucion','notificacion'));

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

        return $pdf->stream('preview.pdf');
    }
}
