<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\PermisoFuncionamiento;
use Illuminate\Http\Request;
use App\Http\Requests\ValidarPermisoFuncionamientoRequest;
use App\Jobs\EnviarPermisoFuncionamientoJob;
use Illuminate\Support\Facades\Auth;
use App\Models\Plantilla;
use App\Models\ProcesoPermiso;
use App\Jobs\GenerarPdfPermisoFuncionamientoJob;
use App\Models\ActividadEconomica;
use App\Models\Configuracion;
use App\Models\Inspeccion;
use App\Models\Institucion;
use App\Models\ProcesoPermisoTarifario;
use App\Models\SolicitudInspeccion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class PermisoFuncionamientoController extends Controller
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
        $estado = $request->input('estado', 'APROBADO');

        if ($estado === 'APROBADO') {
            // =========================
            // INSPECCIONES
            // =========================
            $inspecciones = Inspeccion::with([
                'procesoPermiso.cliente',
                'procesoPermiso.actividadEconomica.parroquia',
                'procesoPermiso.tipoPermiso.tipoInspeccion:id,nombre',
            ])
            ->where('estado', $estado)
            ->whereHas('procesoPermiso', function ($q) {
                $q->whereNull('finished_at');
            })
            ->when($search, function ($query) use ($search) {
                $query->whereHas('procesoPermiso.cliente', function ($q) use ($search) {
                    $q->where('documento', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%");
                });
            })
            ->whereYear('fecha_inspeccion', $year)            
            ->get()            
            ->map(function ($item) {
                $item->tipo = 'inspeccion';
                $item->fecha = $item->fecha_inspeccion;

                return $item;
            });

            // =========================
            // SOLICITUDES INSPECCIÓN
            // =========================
            $solicitudes = SolicitudInspeccion::with([
                'procesoPermiso.cliente',
                'procesoPermiso.actividadEconomica.parroquia',
                'procesoPermiso.tipoPermiso.tipoInspeccion:id,nombre',
            ])
            ->whereHas('procesoPermiso.tipoPermiso', function ($q) {
                $q->whereNull('tipo_inspeccion_id');
            })
            ->whereHas('procesoPermiso', function ($q) {
                $q->whereNull('finished_at');
            })
            ->when($search, function ($query) use ($search) {
                $query->whereHas('procesoPermiso.cliente', function ($q) use ($search) {
                    $q->where('documento', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%");
                });
            })
            ->whereYear('created_at', $year)
            ->get()
            ->map(function ($item) {
                $item->tipo = 'solicitud';
                $item->fecha = $item->created_at;

                return $item;
            });

            // =========================
            // UNIR + PAGINAR
            // =========================
            $collection = $inspecciones
                ->merge($solicitudes)
                ->sortByDesc(function ($item) {
                    return $item->fecha;
                })
                ->values();

            $page = LengthAwarePaginator::resolveCurrentPage();
            $perPage = 10;

            $permisosFuncionamientos = new LengthAwarePaginator(
                $collection
                    ->values()
                    ->slice(($page - 1) * $perPage, $perPage)
                    ->values(),

                $collection->count(),

                $perPage,

                $page,

                [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ]
            );

        } else {

            $permisosFuncionamientos = PermisoFuncionamiento::with([
                'procesoPermiso.cliente',
                'procesoPermiso.actividadEconomica.parroquia',
                'procesoPermiso.tipoPermiso.tipoInspeccion:id,nombre',
            ])
            ->where('estado', $estado)
            ->when($search, function ($query) use ($search) {
                $query->whereHas('procesoPermiso.cliente', function ($q) use ($search) {
                    $q->where('documento', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%");
                });
            })
            ->whereYear('fecha_expedicion', $year)
            ->orderBy('fecha_expedicion')
            ->orderBy('numero')
            ->paginate(10)
            ->withQueryString();

        }
       
        $totales = PermisoFuncionamiento::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN estado = 'EXPEDIDO' THEN 1 ELSE 0 END) as expedidos,
            SUM(CASE WHEN estado = 'ANULADO' THEN 1 ELSE 0 END) as anulados,

            SUM(CASE WHEN estado = 'EXPEDIDO' THEN valor_total ELSE 0 END) as monto_expedidos,
            SUM(CASE WHEN estado = 'ANULADO' THEN valor_total ELSE 0 END) as monto_anulados,

            SUM(valor_total) as monto_total
        ")
        ->whereYear('fecha_expedicion', $year)
        ->first();

        return inertia('permisosFuncionamientos/index', [
            'permisosFuncionamientos' => $permisosFuncionamientos,
            'totales' => $totales,
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
        // 👇 VALIDACIÓN DE SEGURIDAD
        if (!Auth::user()->roles->contains('id', 5)) {
            abort(403, 'No autorizado para crear permisos de funcionamiento.');
        }

        $procesoPermiso->load([
            'cliente.parroquia',
            'actividadEconomica.parroquia',
            'tipoPermiso',
            'tarifarios.tarifario',
            'inspeccionInicial'
        ]);
    
        $valor_inspeccion = 0;
        $valor_reinspeccion = 0;
        $valor_permiso = 0;
        $valor_inspecciones_anteriores = 0;
        $valor_total = 0;

        if ($procesoPermiso->tipoPermiso?->tipo_inspeccion_id) {
            $valor_inspeccion = $procesoPermiso->inspeccionInicial?->valor_inspeccion ?? 0;
            $valor_reinspeccion = $procesoPermiso->reinspecciones->sum('valor_inspeccion');
            $valor_permiso = $procesoPermiso->inspeccionInicial?->valor_permiso ?? 0;
            $valor_inspecciones_anteriores = $procesoPermiso->inspeccionInicial?->valor_inspecciones_anteriores ?? 0;
            $valor_total = $procesoPermiso->inspeccionAprobada?->valor_total ?? 0;
        }

        // Obtener tipo_permiso_id
        $tipoPermisoId = $procesoPermiso->tipo_permiso_id;

        $numero = (
            PermisoFuncionamiento::whereYear('fecha_expedicion', now()->year)
                ->whereHas('procesoPermiso', function ($q) use ($tipoPermisoId) {
                    $q->where('tipo_permiso_id', $tipoPermisoId);
                })
                ->max('numero') ?? 0
        ) + 1;

        $permisoFuncionamiento = [
            'numero' => str_pad($numero, 7, '0', STR_PAD_LEFT),
            'valor_inspeccion' => $valor_inspeccion,
            'valor_reinspeccion' => $valor_reinspeccion,
            'valor_permiso' => $valor_permiso,
            'valor_inspecciones_anteriores' => $valor_inspecciones_anteriores,
            'valor_total' => $valor_total,
        ];

        return inertia('permisosFuncionamientos/create', [
            'procesoPermiso' => $procesoPermiso,
            'permisoFuncionamiento' => $permisoFuncionamiento,
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => (int) $request->input('year', now()->year),
                'estado' => $request->input('estado', 'APROBADO'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ValidarPermisoFuncionamientoRequest $request)
    {
        $procesoPermiso = ProcesoPermiso::findOrFail($request->proceso_permiso_id);
    
        // Obtener tipo_permiso_id
        $tipoPermisoId = $procesoPermiso->tipo_permiso_id;

        $numero = (
            PermisoFuncionamiento::whereYear('fecha_expedicion', now()->year)
                ->whereHas('procesoPermiso', function ($q) use ($tipoPermisoId) {
                    $q->where('tipo_permiso_id', $tipoPermisoId);
                })
                ->max('numero') ?? 0
        ) + 1;

        $codigo_verificacion = strtoupper(Str::random(12));

        $validated = $request->validated();        

        DB::transaction(function () use ($validated, $procesoPermiso, $numero, $codigo_verificacion, $request) {
            PermisoFuncionamiento::create([
                'plantilla_id' => $procesoPermiso->tipoPermiso?->plantilla_id,
                'proceso_permiso_id' => $request->proceso_permiso_id,
                'numero' => $numero,
                'valor_inspeccion' => $request->valor_inspeccion,
                'valor_reinspeccion' => $request->valor_reinspeccion,
                'valor_permiso' => $request->valor_permiso,
                'valor_inspecciones_anteriores' => $request->valor_inspecciones_anteriores,
                'valor_total' => $request->valor_total,
                'fecha_expedicion' => $validated['fecha_expedicion'],
                'fecha_caducidad' => $procesoPermiso->tipoPermiso->tipo_inspeccion_id !== null ? $validated['fecha_caducidad'] : null,
                'fecha_deposito' => $validated['fecha_deposito'],
                'documento_deposito' => $validated['documento_deposito'],
                'p_nombre_proyecto' => $request->p_nombre_proyecto,
                'p_nombre_profesional' => $request->p_nombre_profesional,
                'p_area_total' => $request->p_area_total,
                'observaciones' => $validated['observaciones'],
                'codigo_verificacion' => $codigo_verificacion
            ]);

            $tarifarios = $request->input('proceso_permiso.tarifarios', []);

            foreach ($tarifarios as $tarifa) {
                ProcesoPermisoTarifario::where('id', $tarifa['id'])->update([
                    'area_total' => $tarifa['area_total'],
                    'subtotal' => $tarifa['subtotal'],
                ]);
            }
        });

        return redirect()->route('permisosfuncionamientos.index', [
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
            'estado' => $request->input('estado', 'APROBADO'),
        ])->with('success', 'Permiso de funcionamiento creado correctamente');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, PermisoFuncionamiento $permisoFuncionamiento)
    {
        // 👇 VALIDACIÓN DE SEGURIDAD
        if (!Auth::user()->roles->contains('id', 5)) {
            abort(403, 'No autorizado para editar permisos de funcionamiento.');
        }

        $procesoPermiso = $permisoFuncionamiento->procesoPermiso()
        ->with([
            'cliente.parroquia',
            'actividadEconomica.parroquia',
            'tipoPermiso',
            'tarifarios.tarifario'
        ])
        ->first();

        return inertia('permisosFuncionamientos/edit', [
            'procesoPermiso' => $procesoPermiso,
            'permisoFuncionamiento' => $permisoFuncionamiento,        
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => (int) $request->input('year', now()->year),
                'estado' => $request->input('estado', 'APROBADO'),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ValidarPermisoFuncionamientoRequest $request, PermisoFuncionamiento $permisoFuncionamiento)
    {
        $procesoPermiso = ProcesoPermiso::findOrFail($request->proceso_permiso_id);

        $firmantes = $permisoFuncionamiento->firmantes()->count();

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $procesoPermiso, $firmantes, $request, $permisoFuncionamiento) {
            $permisoFuncionamiento->update([
                'valor_inspeccion' => $request->valor_inspeccion,
                'valor_reinspeccion' => $request->valor_reinspeccion,
                'valor_permiso' => $request->valor_permiso,
                'valor_inspecciones_anteriores' => $request->valor_inspecciones_anteriores,
                'valor_total' => $request->valor_total,
                'fecha_expedicion' => $validated['fecha_expedicion'],
                'fecha_caducidad' => $procesoPermiso->tipoPermiso->tipo_inspeccion_id !== null ? $validated['fecha_caducidad'] : null,
                'fecha_deposito' => $validated['fecha_deposito'],
                'documento_deposito' => $validated['documento_deposito'],
                'p_nombre_proyecto' => $request->p_nombre_proyecto,
                'p_nombre_profesional' => $request->p_nombre_profesional,
                'p_area_total' => $request->p_area_total,
                'observaciones' => $validated['observaciones'],
                'estado_firma' => $firmantes > 0 ? 'PENDIENTE' : 'SIN_FIRMA'
            ]);

            $tarifarios = $request->input('proceso_permiso.tarifarios', []);

            foreach ($tarifarios as $tarifa) {
                ProcesoPermisoTarifario::where('id', $tarifa['id'])->update([
                    'area_total' => $tarifa['area_total'],
                    'subtotal' => $tarifa['subtotal'],
                ]);
            }
        });

        return redirect()->route('permisosfuncionamientos.index', [
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
            'estado' => $request->input('estado', 'APROBADO'),
        ])->with('success', 'Permiso de funcionamiento actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function anular(Request $request, PermisoFuncionamiento $permisoFuncionamiento)
    {
        $permisoFuncionamiento->update([
            'estado' => 'ANULADO'
        ]);

        return redirect()->route('permisosfuncionamientos.index', [
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
            'estado' => $request->input('estado', 'APROBADO'),
        ])->with('success', 'Permiso de funcionamiento anulado correctamente');
    }

    public function comprobarPermisoCasaRentera(Request $request)
    {
        // 1. Validar existencia de actividad económica
        $actividad = ActividadEconomica::with('cliente')
            ->where('e_clave_catastral', $request->clave_catastral)
            ->first();

        if (!$actividad) {
            return [
                'data' => [
                    'permisoCasaRentera' => null,
                    'cliente' => null,
                    'actividad_economica' => null,
                    'mensaje' => 'Clave catastral no encontrada',
                ]
            ];
        }

        $cliente = $actividad->cliente;

        // 2. Buscar permiso
        $permiso = PermisoFuncionamiento::with([
            'procesoPermiso.cliente',
            'procesoPermiso.actividadEconomica',
            'procesoPermiso.tipoPermiso',
        ])
        ->where('estado', 'EXPEDIDO')
        ->whereYear('fecha_expedicion', now()->year)
        ->whereHas('procesoPermiso.actividadEconomica', function ($q) use ($request) {
            $q->where('e_clave_catastral', $request->clave_catastral);
        })
        ->whereHas('procesoPermiso.tarifarios', function ($q) {
            $q->where('tarifario_id', 131);
        })
        ->first();

        // 3. Respuesta final
        return [
            'data' => [
                'permisoCasaRentera' => $permiso,
                'cliente' => $cliente,
                'actividad_economica' => $actividad,
                'mensaje' => $permiso
                    ? null
                    : 'NO ha obtenido el permiso de funcionamiento de casa rentera para ese año',
            ]
        ];
    }

    public function firmar($id)
    {
        $permiso = PermisoFuncionamiento::findOrFail($id);

        GenerarPdfPermisoFuncionamientoJob::dispatch($permiso->id);
    
        return back()->with('success', 'El documento se está firmando en segundo plano');
    }

    public function show(Request $request, PermisoFuncionamiento $permisoFuncionamiento)
    {        
        $permisoFuncionamiento->load([
            'procesoPermiso.cliente.parroquia',
            'procesoPermiso.actividadEconomica.parroquia',
            'procesoPermiso.tipoPermiso',
            'archivos:id,permiso_funcionamiento_id,nombre,ruta,tipo,size'
        ]);  

        return inertia('permisosFuncionamientos/show', [
            'permisoFuncionamiento' => $permisoFuncionamiento,
            'auth_personal_id' => Auth::user()->personal?->id ?? null,
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => (int) $request->input('year', now()->year),
                'estado' => $request->input('estado', 'APROBADO'),
            ],
        ]);
    }

    public function descargar($id)
    {
        $permisoFuncionamiento = PermisoFuncionamiento::findOrFail($id);

        $fecha = Carbon::parse($permisoFuncionamiento->fecha_expedicion);

        if ($permisoFuncionamiento->ruta_pdf_firmado != null){
            $path = storage_path('app/private/' . $permisoFuncionamiento->ruta_pdf_firmado);

            if (!file_exists($path)) {
                return back()->with('error', 'El permiso de funcionamiento no está firmado aún');
            }

            return response()->download(
                $path,
                "Permiso_Funcionamiento_{$permisoFuncionamiento->numero_formateado}_{$fecha->year}_firmado.pdf"
            );
        }else{
            $configuracion = Configuracion::first();

            $url = rtrim($configuracion->link_verificacion_qr, '/') . '/permisosfuncionamientos/' . $permisoFuncionamiento->codigo_verificacion . '/verificar';

            $qrBase64 = base64_encode(
                (new \Endroid\QrCode\Builder\Builder(
                    writer: new \Endroid\QrCode\Writer\PngWriter(),
                    data: $url,
                    size: 150,
                    margin: 10
                ))->build()->getString()
            );

            $institucion = Institucion::first();

            $plantilla = Plantilla::find($permisoFuncionamiento->plantilla_id);
            $permiso = $permisoFuncionamiento;
            $vista = 'pdf.' . $plantilla->vista_blade;
            
            $pdf = Pdf::loadView($vista, compact('institucion','permiso','qrBase64'));

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

            return $pdf->download("Permiso_Funcionamiento_{$permisoFuncionamiento->numero_formateado}_{$fecha->year}.pdf"); 
        }

    }

    public function verificar($codigo)
    {
        $permisoFuncionamiento = PermisoFuncionamiento::where(
            'codigo_verificacion',
            $codigo
        )->firstOrFail();

        return view('verificacion.permisoFuncionamiento', compact('permisoFuncionamiento'));
    }

    public function reenviarCorreo(PermisoFuncionamiento $permisoFuncionamiento)
    {
        if (!$permisoFuncionamiento->ruta_pdf_firmado) {
            return back()->with('error', 'El permiso de funcionamiento no está firmado aún');
        }

        EnviarPermisoFuncionamientoJob::dispatch($permisoFuncionamiento)
            ->onQueue('correos');

        return back()->with('success', 'El correo se está enviando en segundo plano.');
    }

    public function preview($id)
    {
        $permiso = PermisoFuncionamiento::with([
            'procesoPermiso.cliente',
            'procesoPermiso.actividadEconomica' => function ($query) {
                $query->with(['provincia', 'canton', 'parroquia']);
            },
            'procesoPermiso.inspeccionAprobada',
            'procesoPermiso.extintores',
            'plantilla',
        ])->findOrFail($id);

        $url = route('permisosfuncionamientos.verificar', [
            'codigo' => $permiso->codigo_verificacion
        ]);

        $qrBase64 = base64_encode(
            (new \Endroid\QrCode\Builder\Builder(
                writer: new \Endroid\QrCode\Writer\PngWriter(),
                data: $url,
                size: 150,
                margin: 10
            ))->build()->getString()
        );

        $institucion = Institucion::first();
        $plantilla = Plantilla::find($permiso->plantilla_id);
        $vista = 'pdf.' . $plantilla->vista_blade;
        
        $pdf = Pdf::loadView($vista, compact('institucion','permiso','qrBase64'));

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
