<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Inspeccion;
use Illuminate\Http\Request;
use App\Http\Requests\ValidarInspeccionRequest;
use App\Jobs\EnviarInformeInspeccionJob;
use App\Jobs\GenerarPdfInspeccionJob;
use App\Models\CalculoPermiso;
use App\Models\Configuracion;
use App\Models\Personal;
use App\Models\Plantilla;
use App\Models\SolicitudInspeccion;
use Illuminate\Support\Facades\Auth;
use App\Models\Institucion;
use App\Models\ProcesoPermiso;
use App\Models\ProcesoPermisoTarifario;
use App\Services\InspeccionCalculadoraService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class InspeccionController extends Controller
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
        $estado = $request->input('estado', 'GENERADO');

        if ($estado === 'GENERADO') {
            $inspecciones = SolicitudInspeccion::with([
                'procesoPermiso.cliente',
                'procesoPermiso.actividadEconomica.parroquia',
                'procesoPermiso.tipoPermiso.tipoInspeccion:id,nombre',
            ]);
        } else {
            $inspecciones = Inspeccion::with([
                'procesoPermiso.cliente',
                'procesoPermiso.actividadEconomica.parroquia',
                'procesoPermiso.tipoPermiso.tipoInspeccion:id,nombre',
            ]);
        }
        
        $inspecciones = $inspecciones
        ->whereHas('procesoPermiso.tipoPermiso', function ($q) {
            $q->whereNotNull('tipo_inspeccion_id');
        })
        ->when($search, function ($query) use ($search) {
            $query->whereHas('procesoPermiso.cliente', function ($q) use ($search) {
                $q->where('documento', 'like', "%{$search}%")
                  ->orWhere('nombre', 'like', "%{$search}%");
            });
        })
        ->whereYear('fecha_inspeccion', $year)  
        ->when($estado, function ($query) use ($estado) {
            $query->where('estado', $estado);
        })        
        ->orderby('numero')
        ->orderby('fecha_inspeccion')
        ->paginate(10)
        ->withQueryString();

        return inertia('inspecciones/index', [
            'inspecciones' => $inspecciones,
            'auth_personal_id' => Auth::user()->personal?->id ?? null,
            'filters' => [
                'search' => $search,
                'year' => $year,
                'estado' => $estado,
            ],
        ]);
    }

    public function novedades(Request $request)
    {
        $search = trim($request->input('search', ''));
        $year = $request->filled('year') && is_numeric($request->year)
            ? (int) $request->year
            : now()->year;

        $inspecciones = Inspeccion::with([
            'procesoPermiso.cliente',
            'procesoPermiso.actividadEconomica.parroquia',
            'procesoPermiso.tipoPermiso.tipoInspeccion:id,nombre',
        ])
        ->when($search, function ($query) use ($search) {
            $query->whereHas('procesoPermiso.cliente', function ($q) use ($search) {
                $q->where('documento', 'like', "%{$search}%")
                  ->orWhere('nombre', 'like', "%{$search}%");
            });
        })
        ->whereYear('fecha_inspeccion', $year)  
        ->where('procesado', 0)
        ->orderby('fecha_inspeccion')
        ->orderby('numero')
        ->paginate(10)
        ->withQueryString();

        return inertia('inspecciones/novedades', [
            'inspecciones' => $inspecciones,
            'filters' => [
                'search' => $search,
                'year' => $year
            ],
        ]);
    }

    public function calcular(Request $request, InspeccionCalculadoraService $calculadora)
    {
        $procesoPermiso = ProcesoPermiso::findOrFail($request->proceso_permiso_id);

        $valorInspeccion = (float) $request->input('valor_inspeccion');

        $inspeccionId = $request->inspeccionId;

        $resultado = $calculadora->calcular(
            $procesoPermiso,
            $valorInspeccion,
            $inspeccionId
        );

        return response()->json($resultado);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, SolicitudInspeccion $solicitudInspeccion)
    {
        if ($solicitudInspeccion->personal_id !== Auth::user()->personal?->id) {
            abort(403, 'No autorizado para crear este informe.');
        }

        $solicitudInspeccion->load([
            'procesoPermiso.cliente.parroquia',
            'procesoPermiso.actividadEconomica.parroquia',
            'procesoPermiso.tarifarios.tarifario',
            'procesoPermiso.ultimaInspeccion',
        ]);

        $calculoPermiso = CalculoPermiso::where('periodo', now()->year)->first();

        $tarifarios = $solicitudInspeccion->procesoPermiso->tarifarios->map(function ($t) use ($calculoPermiso) {

            $valor = (float) ($t->valor_tarifario ?? 0);

            $factorNivel = match ($t->calc_nivel_riesgo_item?->value ?? $t->calc_nivel_riesgo_item) {
                'AL' => (float) ($calculoPermiso->calc_niv_riesg_alto ?? 0),
                'ME' => (float) ($calculoPermiso->calc_niv_riesg_medio ?? 0),
                'BA' => (float) ($calculoPermiso->calc_niv_riesg_bajo ?? 0),
                default => 0,
            };

            $factorLocal = match ($t->calc_local_item?->value ?? $t->calc_local_item) {
                'GR' => (float) ($calculoPermiso->calc_local_grande ?? 0),
                'ME' => (float) ($calculoPermiso->calc_local_media ?? 0),
                'PE' => (float) ($calculoPermiso->calc_local_pequeno ?? 0),
                'MI' => (float) ($calculoPermiso->calc_local_minimo ?? 0),
                default => 0,
            };

            $factorRecurso = match ($t->calc_recursos_item?->value ?? $t->calc_recursos_item) {
                'ES' => (float) ($calculoPermiso->calc_rec_necesarios_especial ?? 0),
                'ME' => (float) ($calculoPermiso->calc_rec_necesarios_medio ?? 0),
                'MI' => (float) ($calculoPermiso->calc_rec_necesarios_minimo ?? 0),
                default => 0,
            };

            $factorUbicacion = match ($t->calc_ubicacion_item?->value ?? $t->calc_ubicacion_item) {
                'CA' => (float) ($calculoPermiso->calc_ubicacion_canton ?? 0),
                'PA' => (float) ($calculoPermiso->calc_ubicacion_parroquia ?? 0),
                'RU' => (float) ($calculoPermiso->calc_ubicacion_rural ?? 0),
                default => 0,
            };

            return [
                'id' => $t->id,
                'tarifario' => [
                    'nombre' => $t->tarifario?->nombre,
                ],
                'valor_tarifario' => (float) $t->valor_tarifario,
                'valor_fijo' => $t->valor_fijo,                
                'calc_nivel_riesgo_item' => $t->calc_nivel_riesgo_item,
                'calc_local_item' => $t->calc_local_item,
                'calc_recursos_item' => $t->calc_recursos_item,
                'calc_ubicacion_item' => $t->calc_ubicacion_item,
                'calc_nivel_riesgo_valor' => round($valor * $factorNivel, 2),
                'calc_local_valor' => round($valor * $factorLocal, 2),
                'calc_recursos_valor' => round($valor * $factorRecurso, 2),
                'calc_ubicacion_valor' => round($valor * $factorUbicacion, 2),                
                'cantidad' => $t->cantidad,
                'subtotal' => $t->subtotal
            ];
        });

        $solicitudInspeccion->procesoPermiso->setRelation('tarifarios', $tarifarios);

        $ultimoNumero = Inspeccion::whereYear('fecha_inspeccion', now()->year)
        ->max('numero');

        $numero = $ultimoNumero ? $ultimoNumero + 1 : 1;        

        $configuracion = Configuracion::first();

        $valorInspeccion = 0;
        $valorReinspeccion = 0;
        $subtotal = 0;
        $valorTotal = 0;
        $reinspeccion = null;
        
        if ($solicitudInspeccion->tipo_solicitud == 'RE'){
            $valorReinspecciones = Inspeccion::where('proceso_permiso_id', $solicitudInspeccion->procesoPermiso->id)
                ->whereHas('solicitudInspeccion', function ($query) {
                    $query->where('tipo_solicitud', 'RE');
                })
                ->sum('valor_inspeccion');

            $valorInspeccion = $solicitudInspeccion->procesoPermiso->inspeccionInicial->valor_inspeccion;
            $valorReinspeccion = $valorInspeccion * ($configuracion->porcentaje_reinspeccion / 100);
            $subtotal = $valorInspeccion + $valorReinspeccion + $valorReinspecciones;
            $valorTotal = $subtotal;

            $reinspeccion = [
                'valor_inspeccion_inicial' => $valorInspeccion,
                'valor_inspeccion' => $valorReinspeccion,
                'subtotal' => $subtotal,
                'valor_total' => $valorTotal
            ];
        }

        $inspeccionesAnteriores = Inspeccion::whereHas('procesoPermiso', function ($query) use ($solicitudInspeccion) {
                $query->where(
                    'actividad_economica_id',
                    $solicitudInspeccion->procesoPermiso->actividad_economica_id
                );
            })
            ->orderByDesc('fecha_inspeccion')
            ->get();

        return inertia('inspecciones/create', [
            'solicitudInspeccion' => $solicitudInspeccion,
            'numero' => str_pad($numero,7,'0',STR_PAD_LEFT),
            'porcentajeReinspeccion' => $solicitudInspeccion->tipo_solicitud == 'RE' ? $configuracion->porcentaje_reinspeccion : 0,    
            'calculoPermiso' => $calculoPermiso,
            'reinspeccion' => $reinspeccion,
            'inspeccionesAnteriores' => $inspeccionesAnteriores,
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => (int) $request->input('year', now()->year),
                'estado' => $request->input('estado', 'GENERADO'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ValidarInspeccionRequest $request)
    {
        $procesoPermiso = ProcesoPermiso::findOrFail($request->proceso_permiso_id);
        
        // Obtener inspector
        $personal = Personal::where('id', Auth::user()->personal?->id)
        ->firstOrFail();

        $ultimoNumero = Inspeccion::whereYear('fecha_inspeccion', now()->year)
        ->max('numero');

        $numero = $ultimoNumero ? $ultimoNumero + 1 : 1;

        $dias = Configuracion::value('plazo_obtencion_permiso');
        $plazo_permiso = Carbon::now()->addWeekdays($dias);

        $codigo_verificacion = strtoupper(Str::random(12));
        
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $procesoPermiso, $personal, $numero, $plazo_permiso, $codigo_verificacion, $request) {
            
            Inspeccion::create([
                'plantilla_id' => $procesoPermiso->tipoPermiso->tipoInspeccion?->plantilla_id,
                'proceso_permiso_id' => $request->proceso_permiso_id,
                'solicitud_inspeccion_id' => $request->solicitud_inspeccion_id,
                'personal_id' => $personal->id,
                'nombre_personal' => strtoupper($personal->nombre),
                'numero' => $numero,
                'fecha_inspeccion' => now(),
                'e_tipo_funcion' => $request->e_tipo_funcion,
                'e_tipo_funcion_otros' => $request->e_tipo_funcion_otros,
                'e_materiales_almacena' => $request->e_materiales_almacena,
                'e_rgo_estr_hormigon' => $request->e_rgo_estr_hormigon,
                'e_rgo_estr_madera' => $request->e_rgo_estr_madera,
                'e_rgo_estr_mixta' => $request->e_rgo_estr_mixta,
                'e_rgo_estr_metalica' => $request->e_rgo_estr_metalica, 
                'e_rgo_estr_otros' => $request->e_rgo_estr_otros,
                'e_rgo_estr_cub_metalica' => $request->e_rgo_estr_cub_metalica, 
                'e_rgo_estr_cub_madera' => $request->e_rgo_estr_cub_madera,
                'e_rgo_estr_cub_tumbado' => $request->e_rgo_estr_cub_tumbado,
                'e_rgo_estr_cub_fibrocemento' => $request->e_rgo_estr_cub_fibrocemento, 
                'e_rgo_estr_cub_playwood' => $request->e_rgo_estr_cub_playwood,
                'e_rgo_estr_cub_otros' => $request->e_rgo_estr_cub_otros,
                'e_rgo_estr_num_pisos' => $request->e_rgo_estr_num_pisos,
                'e_rgo_estr_piso_ocupa' => $request->e_rgo_estr_piso_ocupa, 
                'e_rgo_estr_area_local' => $request->e_rgo_estr_area_local, 
                'e_rgo_estr_surt_diesel' => $request->e_rgo_estr_surt_diesel, 
                'e_rgo_estr_surt_gasolina' => $request->e_rgo_estr_surt_gasolina, 
                'e_rgo_estr_cap_diesel' => $request->e_rgo_estr_cap_diesel, 
                'e_rgo_estr_cap_gasolina' => $request->e_rgo_estr_cap_gasolina,
                'e_rgo_estr_cap_gas' => $request->e_rgo_estr_cap_gas, 
                'e_rgo_estr_trat_ingnifugo' => $request->e_rgo_estr_trat_ingnifugo,
                'e_rgo_estr_piso_antideslisante' => $request->e_rgo_estr_piso_antideslisante,
                'e_rgo_estr_piso_deslisante' => $request->e_rgo_estr_piso_deslisante, 
                'e_rgo_estr_piso_otro' => $request->e_rgo_estr_piso_otro,
                'e_rgo_estr_norma_inen' => $request->e_rgo_estr_norma_inen,
                'e_rgo_estr_observaciones' => $request->e_rgo_estr_observaciones,
                'e_rgo_elect_buenas' => $request->e_rgo_elect_buenas, 
                'e_rgo_elect_defectuosas' => $request->e_rgo_elect_defectuosas, 
                'e_rgo_elect_malas' => $request->e_rgo_elect_malas,
                'e_rgo_elect_empotradas' => $request->e_rgo_elect_empotradas,
                'e_rgo_elect_sobrepuestas' => $request->e_rgo_elect_sobrepuestas, 
                'e_rgo_elect_volt_110' => $request->e_rgo_elect_volt_110, 
                'e_rgo_elect_volt_220' => $request->e_rgo_elect_volt_220,
                'e_rgo_elect_volt_industrial' => $request->e_rgo_elect_volt_industrial,
                'e_rgo_elect_protec_electrica' => $request->e_rgo_elect_protec_electrica,
                'e_rgo_elect_transformadores' => $request->e_rgo_elect_transformadores,
                'e_rgo_elect_trans_kva' => $request->e_rgo_elect_trans_kva,
                'e_rgo_elect_generadores' => $request->e_rgo_elect_generadores,
                'e_rgo_elect_electricos' => $request->e_rgo_elect_electricos,
                'e_rgo_elect_combustible' => $request->e_rgo_elect_combustible,
                'e_rgo_salud_epp_casco' => $request->e_rgo_salud_epp_casco,
                'e_rgo_salud_epp_gafas' => $request->e_rgo_salud_epp_gafas,
                'e_rgo_salud_epp_calzado' => $request->e_rgo_salud_epp_calzado,
                'e_rgo_salud_epp_vestimenta' => $request->e_rgo_salud_epp_vestimenta,
                'e_rgo_salud_epp_guantes' => $request->e_rgo_salud_epp_guantes,
                'e_rgo_salud_epp_orejeras' => $request->e_rgo_salud_epp_orejeras,
                'e_rgo_salud_observaciones' => $request->e_rgo_salud_observaciones,
                'e_sis_incend_pqs' => $request->e_sis_incend_pqs,
                'e_sis_incend_co2' => $request->e_sis_incend_co2,
                'e_sis_incend_h2o' => $request->e_sis_incend_h2o,
                'e_sis_incend_capacidad' => $request->e_sis_incend_capacidad,
                'e_sis_incend_det_humo' => $request->e_sis_incend_det_humo,
                'e_sis_incend_lamp_emer' => $request->e_sis_incend_lamp_emer,
                'e_sis_incend_gabinete' => $request->e_sis_incend_gabinete,
                'e_sis_incend_sprinklers' => $request->e_sis_incend_sprinklers,
                'e_sis_incend_alarm_incendio' => $request->e_sis_incend_alarm_incendio,
                'e_sis_incend_bomb_hidraulica' => $request->e_sis_incend_bomb_hidraulica,
                'e_sis_incend_siamesa' => $request->e_sis_incend_siamesa,
                'e_sis_incend_escalera_emer' => $request->e_sis_incend_escalera_emer,
                'e_sis_incend_puert_corta_fuego' => $request->e_sis_incend_puert_corta_fuego,
                'e_sis_incend_senal_emer' => $request->e_sis_incend_senal_emer,
                'e_sis_incend_puert_emer' => $request->e_sis_incend_puert_emer,
                'e_sis_incend_tanq_reserv_agua' => $request->e_sis_incend_tanq_reserv_agua,
                'e_sis_incend_cap_tanq_reserv' => $request->e_sis_incend_cap_tanq_reserv,
                'v_tipo_combustible' => $request->v_tipo_combustible,
                'v_cantidad_combustible' => $request->v_cantidad_combustible,
                'v_cantidad_cilindros' => $request->v_cantidad_cilindros,
                'v_origen_transporte' => $request->v_origen_transporte,
                'v_destino_transporte' => $request->v_destino_transporte,
                'v_tipo_extintor' => $request->v_tipo_extintor,
                'v_capacidad_extintor' => $request->v_capacidad_extintor,
                'v_triangulos_seguridad' => $request->v_triangulos_seguridad,
                'v_materiales_peligrosos' => $request->v_materiales_peligrosos,
                'v_control_derrames' => $request->v_control_derrames,
                'v_descarga_tierra' => $request->v_descarga_tierra,
                'v_arrestallamas' => $request->v_arrestallamas,
                'v_senalizacion' => $request->v_senalizacion,
                'v_botiquin' => $request->v_botiquin,
                'v_luces_stop' => $request->v_luces_stop,
                'v_luces_parking' => $request->v_luces_parking,
                'medidas_obligatorias' => $validated['medidas_obligatorias'],
                'extintores_observaciones' => $request->extintores_observaciones,
                'valor_permiso' => $request->valor_permiso,
                'valor_inspeccion' => $request->valor_inspeccion,
                'valor_descuento' => $request->valor_descuento,
                'valor_inspecciones_anteriores' => $request->valor_inspecciones_anteriores,
                'subtotal' => $request->subtotal,
                'valor_total' => $request->valor_total,
                'estado' => $validated['estado_informe'],
                'procesado' => $request->estado_informe == 'REINSPECCION' ? 0 : 1,
                'plazo_permiso' => $request->estado_informe == 'APROBADO' ? $plazo_permiso : null,
                'codigo_verificacion' => $codigo_verificacion
            ]);

            if (!empty($request->extintores)) {
                $procesoPermiso = ProcesoPermiso::findOrFail($request->proceso_permiso_id);

                $procesoPermiso->extintores()->createMany(
                    collect($request->extintores)->map(fn ($e) => [
                        ...$e,
                        'periodo' => now()->year,
                    ])->toArray()
                );
            }

            $tarifarios = $request->input('proceso_permiso.tarifarios', []);

            foreach ($tarifarios as $tarifa) {
                ProcesoPermisoTarifario::where('id', $tarifa['id'])->update([
                    'calc_nivel_riesgo_item' => $tarifa['calc_nivel_riesgo_item'],
                    'calc_nivel_riesgo_valor' => $tarifa['calc_nivel_riesgo_valor'],
                    'calc_local_item' => $tarifa['calc_local_item'],
                    'calc_local_valor' => $tarifa['calc_local_valor'],
                    'calc_recursos_item' => $tarifa['calc_recursos_item'],
                    'calc_recursos_valor' => $tarifa['calc_recursos_valor'],
                    'calc_ubicacion_item' => $tarifa['calc_ubicacion_item'],
                    'calc_ubicacion_valor' => $tarifa['calc_ubicacion_valor'],
                    'cantidad' => $tarifa['cantidad'],
                    'subtotal' => $tarifa['subtotal'],
                ]);
            }
        });

        return redirect()->route('inspecciones.index', [
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
            'estado' => $request->input('estado', 'GENERADO'),
        ])->with('success', 'Informe de inspección creada correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Inspeccion $inspeccion)
    {        
        $inspeccion->load([
            'procesoPermiso.cliente.parroquia',
            'procesoPermiso.actividadEconomica.parroquia',
            'archivos:id,inspeccion_id,nombre,ruta,tipo,size'
        ]);  

        return inertia('inspecciones/show', [
            'inspeccion' => $inspeccion,
            'auth_personal_id' => Auth::user()->personal?->id ?? null,
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
    public function edit(Request $request, Inspeccion $inspeccion)
    {
        // 👇 VALIDACIÓN DE SEGURIDAD
        if ($inspeccion->personal_id !== Auth::user()->personal?->id) {
            abort(403, 'No autorizado para editar este informe.');
        }

        $procesoPermiso = $inspeccion->procesoPermiso()
            ->with([
                'solicitudInspeccion',
                'cliente.parroquia',
                'actividadEconomica.parroquia',
                'tarifarios.tarifario'
            ])->first();

        $calculoPermiso = CalculoPermiso::where('periodo', now()->year)->first();

        $tarifarios = $procesoPermiso->tarifarios->map(function ($t) use ($calculoPermiso) {

            $valor = (float) ($t->valor_tarifario ?? 0);

            $factorNivel = match ($t->calc_nivel_riesgo_item?->value ?? $t->calc_nivel_riesgo_item) {
                'AL' => (float) ($calculoPermiso->calc_niv_riesg_alto ?? 0),
                'ME' => (float) ($calculoPermiso->calc_niv_riesg_medio ?? 0),
                'BA' => (float) ($calculoPermiso->calc_niv_riesg_bajo ?? 0),
                default => 0,
            };

            $factorLocal = match ($t->calc_local_item?->value ?? $t->calc_local_item) {
                'GR' => (float) ($calculoPermiso->calc_local_grande ?? 0),
                'ME' => (float) ($calculoPermiso->calc_local_media ?? 0),
                'PE' => (float) ($calculoPermiso->calc_local_pequeno ?? 0),
                'MI' => (float) ($calculoPermiso->calc_local_minimo ?? 0),
                default => 0,
            };

            $factorRecurso = match ($t->calc_recursos_item?->value ?? $t->calc_recursos_item) {
                'ES' => (float) ($calculoPermiso->calc_rec_necesarios_especial ?? 0),
                'ME' => (float) ($calculoPermiso->calc_rec_necesarios_medio ?? 0),
                'MI' => (float) ($calculoPermiso->calc_rec_necesarios_minimo ?? 0),
                default => 0,
            };

            $factorUbicacion = match ($t->calc_ubicacion_item?->value ?? $t->calc_ubicacion_item) {
                'CA' => (float) ($calculoPermiso->calc_ubicacion_canton ?? 0),
                'PA' => (float) ($calculoPermiso->calc_ubicacion_parroquia ?? 0),
                'RU' => (float) ($calculoPermiso->calc_ubicacion_rural ?? 0),
                default => 0,
            };

            return [
                'id' => $t->id,
                'tarifario' => [
                    'nombre' => $t->tarifario?->nombre,
                ],
                'valor_tarifario' => (float) $t->valor_tarifario,
                'valor_fijo' => $t->valor_fijo,                
                'calc_nivel_riesgo_item' => $t->calc_nivel_riesgo_item,
                'calc_local_item' => $t->calc_local_item,
                'calc_recursos_item' => $t->calc_recursos_item,
                'calc_ubicacion_item' => $t->calc_ubicacion_item,
                'calc_nivel_riesgo_valor' => round($valor * $factorNivel, 2),
                'calc_local_valor' => round($valor * $factorLocal, 2),
                'calc_recursos_valor' => round($valor * $factorRecurso, 2),
                'calc_ubicacion_valor' => round($valor * $factorUbicacion, 2),                
                'cantidad' => $t->cantidad,
                'subtotal' => $t->subtotal
            ];
        });

        $procesoPermiso->setRelation('tarifarios', $tarifarios);

        $configuracion = Configuracion::first();

        $valorInspeccion = 0;
        $valorReinspeccion = 0;
        $subtotal = 0;
        $valorTotal = 0;
        $reinspeccion = null;
        
        if ($procesoPermiso->solicitudInspeccion?->tipo_solicitud == 'RE'){
            $valorReinspecciones = Inspeccion::where('proceso_permiso_id', $procesoPermiso->id)
                ->whereHas('solicitudInspeccion', function ($query) {
                    $query->where('tipo_solicitud', 'RE');
                })
                ->sum('valor_inspeccion');

            $valorInspeccion = $procesoPermiso->inspeccionInicial->valor_inspeccion;
            $valorReinspeccion = $valorInspeccion * ($configuracion->porcentaje_reinspeccion / 100);
            $subtotal = $valorInspeccion + $valorReinspeccion + $valorReinspecciones;
            $valorTotal = $subtotal;

            $reinspeccion = [
                'valor_inspeccion_inicial' => $valorInspeccion,
                'valor_inspeccion' => $valorReinspeccion,
                'subtotal' => $subtotal,
                'valor_total' => $valorTotal
            ];
        }

        $inspeccionesAnteriores = Inspeccion::whereHas('procesoPermiso', function ($query) use ($procesoPermiso) {
                $query->where(
                    'actividad_economica_id',
                    $procesoPermiso->actividad_economica_id
                );
            })
            ->where('id', '!=', $inspeccion->id)
            ->orderByDesc('fecha_inspeccion')
            ->get();

        return inertia('inspecciones/edit', [
            'procesoPermiso' => $procesoPermiso,
            'inspeccion' => $inspeccion,
            'porcentajeReinspeccion' => $procesoPermiso->solicitudInspeccion->tipo_solicitud == 'RE' ? $configuracion->porcentaje_reinspeccion : 0,    
            'calculoPermiso' => $calculoPermiso,
            'reinspeccion' => $reinspeccion, 
            'inspeccionesAnteriores' => $inspeccionesAnteriores,     
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
    public function update(ValidarInspeccionRequest $request, Inspeccion $inspeccion)
    {
        // Obtener inspector
        $personal = Personal::where('id', Auth::user()->personal?->id)
        ->firstOrFail();

        $firmantes = $inspeccion->firmantes()->count();

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $request, $personal, $inspeccion, $firmantes) {
            $inspeccion->update([
                'personal_id' => $personal->id,
                'nombre_personal' => strtoupper($personal->nombre),
                'e_tipo_funcion' => $request->e_tipo_funcion,
                'e_tipo_funcion_otros' => $request->e_tipo_funcion_otros,
                'e_materiales_almacena' => $request->e_materiales_almacena,
                'e_rgo_estr_hormigon' => $request->e_rgo_estr_hormigon,
                'e_rgo_estr_madera' => $request->e_rgo_estr_madera,
                'e_rgo_estr_mixta' => $request->e_rgo_estr_mixta,
                'e_rgo_estr_metalica' => $request->e_rgo_estr_metalica, 
                'e_rgo_estr_otros' => $request->e_rgo_estr_otros,
                'e_rgo_estr_cub_metalica' => $request->e_rgo_estr_cub_metalica, 
                'e_rgo_estr_cub_madera' => $request->e_rgo_estr_cub_madera,
                'e_rgo_estr_cub_tumbado' => $request->e_rgo_estr_cub_tumbado,
                'e_rgo_estr_cub_fibrocemento' => $request->e_rgo_estr_cub_fibrocemento, 
                'e_rgo_estr_cub_playwood' => $request->e_rgo_estr_cub_playwood,
                'e_rgo_estr_cub_otros' => $request->e_rgo_estr_cub_otros,
                'e_rgo_estr_num_pisos' => $request->e_rgo_estr_num_pisos,
                'e_rgo_estr_piso_ocupa' => $request->e_rgo_estr_piso_ocupa, 
                'e_rgo_estr_area_local' => $request->e_rgo_estr_area_local, 
                'e_rgo_estr_surt_diesel' => $request->e_rgo_estr_surt_diesel, 
                'e_rgo_estr_surt_gasolina' => $request->e_rgo_estr_surt_gasolina, 
                'e_rgo_estr_cap_diesel' => $request->e_rgo_estr_cap_diesel, 
                'e_rgo_estr_cap_gasolina' => $request->e_rgo_estr_cap_gasolina,
                'e_rgo_estr_cap_gas' => $request->e_rgo_estr_cap_gas, 
                'e_rgo_estr_trat_ingnifugo' => $request->e_rgo_estr_trat_ingnifugo,
                'e_rgo_estr_piso_antideslisante' => $request->e_rgo_estr_piso_antideslisante,
                'e_rgo_estr_piso_deslisante' => $request->e_rgo_estr_piso_deslisante, 
                'e_rgo_estr_piso_otro' => $request->e_rgo_estr_piso_otro,
                'e_rgo_estr_norma_inen' => $request->e_rgo_estr_norma_inen,
                'e_rgo_estr_observaciones' => $request->e_rgo_estr_observaciones,
                'e_rgo_elect_buenas' => $request->e_rgo_elect_buenas, 
                'e_rgo_elect_defectuosas' => $request->e_rgo_elect_defectuosas, 
                'e_rgo_elect_malas' => $request->e_rgo_elect_malas,
                'e_rgo_elect_empotradas' => $request->e_rgo_elect_empotradas,
                'e_rgo_elect_sobrepuestas' => $request->e_rgo_elect_sobrepuestas, 
                'e_rgo_elect_volt_110' => $request->e_rgo_elect_volt_110, 
                'e_rgo_elect_volt_220' => $request->e_rgo_elect_volt_220,
                'e_rgo_elect_volt_industrial' => $request->e_rgo_elect_volt_industrial,
                'e_rgo_elect_protec_electrica' => $request->e_rgo_elect_protec_electrica,
                'e_rgo_elect_transformadores' => $request->e_rgo_elect_transformadores,
                'e_rgo_elect_trans_kva' => $request->e_rgo_elect_trans_kva,
                'e_rgo_elect_generadores' => $request->e_rgo_elect_generadores,
                'e_rgo_elect_electricos' => $request->e_rgo_elect_electricos,
                'e_rgo_elect_combustible' => $request->e_rgo_elect_combustible,
                'e_rgo_salud_epp_casco' => $request->e_rgo_salud_epp_casco,
                'e_rgo_salud_epp_gafas' => $request->e_rgo_salud_epp_gafas,
                'e_rgo_salud_epp_calzado' => $request->e_rgo_salud_epp_calzado,
                'e_rgo_salud_epp_vestimenta' => $request->e_rgo_salud_epp_vestimenta,
                'e_rgo_salud_epp_guantes' => $request->e_rgo_salud_epp_guantes,
                'e_rgo_salud_epp_orejeras' => $request->e_rgo_salud_epp_orejeras,
                'e_rgo_salud_observaciones' => $request->e_rgo_salud_observaciones,
                'e_sis_incend_pqs' => $request->e_sis_incend_pqs,
                'e_sis_incend_co2' => $request->e_sis_incend_co2,
                'e_sis_incend_h2o' => $request->e_sis_incend_h2o,
                'e_sis_incend_capacidad' => $request->e_sis_incend_capacidad,
                'e_sis_incend_det_humo' => $request->e_sis_incend_det_humo,
                'e_sis_incend_lamp_emer' => $request->e_sis_incend_lamp_emer,
                'e_sis_incend_gabinete' => $request->e_sis_incend_gabinete,
                'e_sis_incend_sprinklers' => $request->e_sis_incend_sprinklers,
                'e_sis_incend_alarm_incendio' => $request->e_sis_incend_alarm_incendio,
                'e_sis_incend_bomb_hidraulica' => $request->e_sis_incend_bomb_hidraulica,
                'e_sis_incend_siamesa' => $request->e_sis_incend_siamesa,
                'e_sis_incend_escalera_emer' => $request->e_sis_incend_escalera_emer,
                'e_sis_incend_puert_corta_fuego' => $request->e_sis_incend_puert_corta_fuego,
                'e_sis_incend_senal_emer' => $request->e_sis_incend_senal_emer,
                'e_sis_incend_puert_emer' => $request->e_sis_incend_puert_emer,
                'e_sis_incend_tanq_reserv_agua' => $request->e_sis_incend_tanq_reserv_agua,
                'e_sis_incend_cap_tanq_reserv' => $request->e_sis_incend_cap_tanq_reserv,
                'v_tipo_combustible' => $request->v_tipo_combustible,
                'v_cantidad_combustible' => $request->v_cantidad_combustible,
                'v_cantidad_cilindros' => $request->v_cantidad_cilindros,
                'v_origen_transporte' => $request->v_origen_transporte,
                'v_destino_transporte' => $request->v_destino_transporte,
                'v_tipo_extintor' => $request->v_tipo_extintor,
                'v_capacidad_extintor' => $request->v_capacidad_extintor,
                'v_triangulos_seguridad' => $request->v_triangulos_seguridad,
                'v_materiales_peligrosos' => $request->v_materiales_peligrosos,
                'v_control_derrames' => $request->v_control_derrames,
                'v_descarga_tierra' => $request->v_descarga_tierra,
                'v_arrestallamas' => $request->v_arrestallamas,
                'v_senalizacion' => $request->v_senalizacion,
                'v_botiquin' => $request->v_botiquin,
                'v_luces_stop' => $request->v_luces_stop,
                'v_luces_parking' => $request->v_luces_parking,
                'medidas_obligatorias' => $validated['medidas_obligatorias'],
                'extintores_observaciones' => $request->extintores_observaciones,
                'valor_permiso' => $request->valor_permiso,
                'valor_inspeccion' => $request->valor_inspeccion,
                'valor_descuento' => $request->valor_descuento,
                'valor_inspecciones_anteriores' => $request->valor_inspecciones_anteriores,
                'subtotal' => $request->subtotal,
                'valor_total' => $request->valor_total,
                'estado' => $validated['estado_informe'],
                'procesado' => $request->estado_informe == 'APROBADO' ? 1 : 0,
                'estado_firma' => $firmantes > 0 ? 'PENDIENTE' : 'SIN_FIRMA'
            ]);

            if (!empty($request->extintores)) {
                $procesoPermiso = $inspeccion->procesoPermiso;

                $idsEnviados = collect($request->extintores)
                    ->pluck('id')
                    ->filter()
                    ->toArray();

                // 1. ELIMINAR LOS QUE YA NO EXISTEN
                $procesoPermiso->extintores()
                    ->whereNotIn('id', $idsEnviados)
                    ->delete();

                // 2. CREAR O ACTUALIZAR
                foreach ($request->extintores as $ext) {
                    $procesoPermiso->extintores()->updateOrCreate(
                        [
                            'id' => $ext['id'] ?? null
                        ],
                        [
                            'periodo' => now()->year,
                            'numero' => $ext['numero'],
                            'tipo' => $ext['tipo'],
                            'capacidad' => $ext['capacidad'],
                            'estado' => $ext['estado'],
                        ]
                    );
                }
            }

            $tarifarios = $request->input('proceso_permiso.tarifarios', []);

            foreach ($tarifarios as $tarifa) {
                ProcesoPermisoTarifario::where('id', $tarifa['id'])->update([
                    'calc_nivel_riesgo_item' => $tarifa['calc_nivel_riesgo_item'],
                    'calc_nivel_riesgo_valor' => $tarifa['calc_nivel_riesgo_valor'],
                    'calc_local_item' => $tarifa['calc_local_item'],
                    'calc_local_valor' => $tarifa['calc_local_valor'],
                    'calc_recursos_item' => $tarifa['calc_recursos_item'],
                    'calc_recursos_valor' => $tarifa['calc_recursos_valor'],
                    'calc_ubicacion_item' => $tarifa['calc_ubicacion_item'],
                    'calc_ubicacion_valor' => $tarifa['calc_ubicacion_valor'],
                    'cantidad' => $tarifa['cantidad'],
                    'subtotal' => $tarifa['subtotal'],
                ]);
            }
        });

        return redirect()->route('inspecciones.index', [
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
            'estado' => $request->input('estado', 'GENERADO'),
        ])->with('success', 'Informe de inspección actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Inspeccion $inspeccion)
    {
        $inspeccion->firmantes()->delete();
    
        $inspeccion->delete();

        return redirect()->route('inspecciones.index', [
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
            'estado' => $request->input('estado', 'GENERADO'),
        ])->with('success', 'Informe de inspección eliminado correctamente');
    }

    public function firmar($id)
    {
        $inspeccion = Inspeccion::findOrFail($id);

        GenerarPdfInspeccionJob::dispatch($inspeccion->id);
    
        return back()->with('success', 'El documento se está firmando en segundo plano');
    }

    public function descargarComprobante($id)
    {
        $inspeccion = Inspeccion::findOrFail($id);

        $fecha = Carbon::parse($inspeccion->fecha_inspeccion);
        
        $institucion = Institucion::first();

        $plantilla = null;

        if ($inspeccion->procesoPermiso->actividadEconomica->tipo_actividad == 'E'){
            $plantilla = Plantilla::where('nombre', 'COMPROBANTE DE INSPECCION A ESTABLECIMIENTOS')
                ->where('predeterminado', 1)
                ->first();
        }else{
            $plantilla = Plantilla::where('nombre', 'COMPROBANTE DE INSPECCION A VEHICULOS')
                ->where('predeterminado', 1)
                ->first();
        }    

        $vista = 'pdf.' . $plantilla->vista_blade;
        
        $pdf = Pdf::loadView($vista, compact('institucion','inspeccion'));

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

        return $pdf->download("Comprobante_Inspeccion_{$inspeccion->numero_formateado}_{$fecha->year}.pdf"); 
    }

    public function descargar($id)
    {
        $inspeccion = Inspeccion::findOrFail($id);

        $fecha = Carbon::parse($inspeccion->fecha_inspeccion);

        if ($inspeccion->ruta_pdf_firmado != null){    
            $path = storage_path('app/private/' . $inspeccion->ruta_pdf_firmado);

            if (!file_exists($path)) {
                return back()->with('error', 'El informe de inspección no está firmado aún');
            }

            return response()->download(
                $path,
                "Informe_Inspeccion_{$inspeccion->numero_formateado}_{$fecha->year}_firmado.pdf"
            );
        }else{
            $configuracion = Configuracion::first();

            $url = rtrim($configuracion->link_verificacion_qr, '/') . '/inspecciones/' . $inspeccion->codigo_verificacion . '/verificar';         

            $qrBase64 = base64_encode(
                (new \Endroid\QrCode\Builder\Builder(
                    writer: new \Endroid\QrCode\Writer\PngWriter(),
                    data: $url,
                    size: 150,
                    margin: 10
                ))->build()->getString()
            );

            $institucion = Institucion::first();
            $plantilla = Plantilla::find($inspeccion->plantilla_id);
            $vista = 'pdf.' . $plantilla->vista_blade;
            
            $pdf = Pdf::loadView($vista, compact('institucion','inspeccion','qrBase64'));

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

            return $pdf->download("Informe_Inspeccion_{$inspeccion->numero_formateado}_{$fecha->year}.pdf"); 
        }
    }

    public function verificar($codigo)
    {
        $inspeccion = Inspeccion::where(
            'codigo_verificacion',
            $codigo
        )->firstOrFail();

        return view('verificacion.inspeccion', compact('inspeccion'));
    }

    public function reenviarCorreo(Inspeccion $inspeccion)
    {
        if (!$inspeccion->ruta_pdf_firmado) {
            return back()->with('error', 'El informe de inspección no está firmado aún');
        }

        EnviarInformeInspeccionJob::dispatch($inspeccion)
            ->onQueue('correos');

        return back()->with('success', 'El correo se está enviando en segundo plano.');
    }

    public function preview($id)
    {
        $configuracion = Configuracion::first();

        $inspeccion = Inspeccion::findOrFail($id);

        $url = rtrim($configuracion->link_verificacion_qr, '/') . '/inspecciones/' . $inspeccion->codigo_verificacion . '/verificar';         

        $qrBase64 = base64_encode(
            (new \Endroid\QrCode\Builder\Builder(
                writer: new \Endroid\QrCode\Writer\PngWriter(),
                data: $url,
                size: 150,
                margin: 10
            ))->build()->getString()
        );

        $institucion = Institucion::first();
        $plantilla = Plantilla::find($inspeccion->plantilla_id);
        $vista = 'pdf.' . $plantilla->vista_blade;
        
        $pdf = Pdf::loadView($vista, compact('institucion','inspeccion','qrBase64'));

        return $pdf->stream('preview.pdf');
    }
}
