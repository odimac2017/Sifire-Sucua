<?php

namespace App\Http\Controllers;

use App\Models\ParteEmergencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ValidarParteEmergenciaRequest;
use App\Jobs\GenerarPdfParteEmergenciaJob;
use App\Models\CategoriaEmergencia;
use App\Models\Institucion;
use App\Models\Insumo;
use App\Models\Numeracion;
use App\Models\ParteEmergenciaCategoria;
use App\Models\Personal;
use App\Models\PersonalPuesto;
use App\Models\Plantilla;
use App\Models\Provincia;
use App\Services\MapaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ParteEmergenciaController extends Controller
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

        $partesEmergencias = ParteEmergencia::with([
            'categorias',
            'parroquia:id,nombre',
            'personal.user:id,avatar'
        ])
        ->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                ->orWhereHas('categorias', function ($categoria) use ($search) {
                    $categoria->where('categoria1', 'like', "%{$search}%")
                        ->orWhere('categoria2', 'like', "%{$search}%");
                });
            });
        })
        ->whereYear('fecha_llamada', $year)
        ->orderBy('fecha_llamada')
        ->orderBy('secuencia')
        ->paginate(10)
        ->withQueryString();
        
        return inertia('partesEmergencias/index', [
            'partesEmergencias' => $partesEmergencias,
            'auth_personal_id' => Auth::user()->personal?->id ?? null,
            'filters' => [
                'search' => $search,
                'year' => $year,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {   
        $coordenadas = Institucion::select('latitud','longitud')
        ->first();

        $categorias = CategoriaEmergencia::with('childrens')
        ->whereNull('parent_id')
        ->get()
        ->map(function ($parent) {
            return [
                'label' => $parent->nombre,
                'options' => $parent->childrens->map(function ($child) {
                    return [
                        'id' => $child->id,
                        'nombre' => $child->nombre
                    ];
                })->values()
            ];
        });

        $provincias = Provincia::select('id','nombre')
        ->orderBy('codigo')
        ->get();

        $personales = Personal::with('titulo')
        ->whereHas('user.roles', function($query) {
            $query->where('rol_id', 6);
        })
        ->where('estado', 'ACTIVO')
        ->orderBy('apellidos')
        ->orderBy('nombres')
        ->get()
        ->map(fn ($p) => [
            'id' => $p->id,
            'nombre' => strtoupper($p->nombre)
        ]);        

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

        $insumos = Insumo::where('estado', 'ACTIVO')
        ->orderBy('nombre')
        ->get()
        ->map(fn ($p) => [
            'id' => $p->id,
            'nombre' => $p->nombre,
            'presentacion' => $p->presentacion
        ]);   

        // 🔢 Obtener el último número del día (opcional: por fecha o global)
        $ultimaSecuencia = ParteEmergencia::whereYear('fecha_llamada', now()->year)
        ->max('secuencia');

        $secuencia = $ultimaSecuencia ? $ultimaSecuencia + 1 : 1;

        $formato = Numeracion::where('nombre', 'PARTE EMERGENCIAS')
        ->value('formato');

        // 🧾 Construir número
        $numero = $formato . '-' . str_pad($secuencia, 7, '0', STR_PAD_LEFT) . '-' . now()->year;

        return inertia('partesEmergencias/create', [
            'coordenadas' => $coordenadas,
            'categorias' => $categorias,
            'provincias' => $provincias,
            'personales' => $personales,
            'personalesPuestos' => $personalesPuestos,
            'insumos' => $insumos,
            'numero' => $numero,
            'secuencia' => $secuencia,
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => (int) $request->input('year', now()->year),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ValidarParteEmergenciaRequest $request, MapaService $mapaService)
    {
        $plantillaId = Plantilla::where('nombre', 'PARTE DE EMERGENCIA')
        ->where('predeterminado', 1)
        ->value('id');

        $personalPuesto = PersonalPuesto::with(['personal.titulo','puesto'])
        ->where('personal_id', Auth::user()->personal->id)
        ->where('predeterminado', 1)
        ->firstOrFail();

        /*
        $personalPuestoRevisa = PersonalPuesto::with(['personal.titulo','puesto'])
        ->findOrFail($request->personal_puesto_revisa_id);*/

        /*
        // 🔢 Obtener el último número del día (opcional: por fecha o global)
        $ultimaSecuencia = ParteEmergencia::whereYear('fecha_llamada', now()->year)
        ->max('secuencia');

        $secuencia = $ultimaSecuencia ? $ultimaSecuencia + 1 : 1;*/

        $formato = Numeracion::where('nombre', 'PARTE EMERGENCIAS')
        ->value('formato');

        // 🧾 Construir número
        $numero = $formato . '-' . str_pad($request->secuencia, 7, '0', STR_PAD_LEFT) . '-' . now()->year;

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $plantillaId, $personalPuesto, $numero, $mapaService, $request) {

            $validated['plantilla_id'] = $plantillaId;
            $validated['personal_id'] = Auth::user()->personal->id;
            $validated['personal_puesto_id'] = $personalPuesto->id;
            $validated['nombre_personal'] = strtoupper($personalPuesto->personal->nombre);
            $validated['puesto_personal'] = strtoupper($personalPuesto->puesto->nombre);
            /*$validated['nombre_revisa'] = strtoupper($personalPuestoRevisa->personal->nombre);
            $validated['puesto_revisa'] = strtoupper($personalPuestoRevisa->puesto->nombre);*/
            $validated['nombre_radio'] = strtoupper(
                Personal::findOrFail($validated['personal_radio_id'])->nombre
            );    
            $validated['numero'] = $numero;
            $validated['secuencia'] = $request->secuencia;

            if (!empty($validated['latitud']) && !empty($validated['longitud'])) {
                $rutaMapa = $mapaService->generarMapa(
                    $validated['latitud'],
                    $validated['longitud']
                );
                $validated['ruta_mapa'] = $rutaMapa;
            }
            
            // Crear parte
            $parteEmergencia = ParteEmergencia::create($validated);

            // Obtener categorias seleccionadas
            $categorias = CategoriaEmergencia::with('parent')
            ->whereIn('id', $validated['categoria_emergencia_id'])
                ->get(['id','parent_id','nombre']);

            $dataInsert = $categorias->map(function ($categoria) use ($parteEmergencia) {
                return [
                    'parte_emergencia_id' => $parteEmergencia->id,
                    'categoria_emergencia_id' => $categoria->id,
                    'categoria1' => optional($categoria->parent)->nombre,
                    'categoria2' => $categoria->nombre,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });

            ParteEmergenciaCategoria::insert($dataInsert->toArray());

            $personales = Personal::whereIn('id', $validated['personalesParte'] ?? [])
                ->get()
                ->mapWithKeys(fn ($p) => [
                    $p->id => [
                        'nombre_personal' => strtoupper($p->nombre)
                    ]
                ])
                ->toArray();

            $parteEmergencia->personales()->sync($personales);

            $personalesQapData = collect($validated['personalesQapParte'] ?? []);

            $personales = Personal::with('titulo')
                ->whereIn('id', $personalesQapData->pluck('personal_id'))
                ->get()
                ->pluck('nombre', 'id');

            $parteEmergencia->personalesQap()->sync(
                $personalesQapData->mapWithKeys(function ($item) use ($personales) {
                    return [
                        $item['personal_id'] => [
                            'nombre_personal' => strtoupper($personales[$item['personal_id']]) ?? null,
                            'desde' => $item['desde'],
                            'hasta' => $item['hasta'],
                            'tiempo_total' => $item['tiempo_total'],
                        ]
                    ];
                })->toArray()
            );        

            $parteEmergencia->insumos()->sync(
                collect($validated['insumosParte'] ?? [])
                    ->mapWithKeys(fn ($item) => [
                        $item['insumo_id'] => [
                            'cantidad' => $item['cantidad'],
                        ]
                    ])
                    ->toArray()
            );
        });

        return redirect()->route('partesemergencias.index', [
           'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
        ])->with('success', 'Parte de emergencia creado correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, ParteEmergencia $parteEmergencia)
    {        
        $parteEmergencia->load([
            'parroquia',
            'archivos:id,parte_emergencia_id,nombre,ruta,tipo,size'
        ]);        

        return inertia('partesEmergencias/show', [
            'parteEmergencia' => $parteEmergencia,
            'auth_personal_id' => Auth::user()->personal?->id ?? null,
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => (int) $request->input('year', now()->year),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, ParteEmergencia $parteEmergencia)
    {
        // 👇 VALIDACIÓN DE SEGURIDAD
        if ($parteEmergencia->personal_id != Auth::user()->personal->id) {
            abort(403, 'No autorizado para editar este parte de emergencia.');
        }

        $categoriasParte = $parteEmergencia->categorias
            ->pluck('categoria_emergencia_id')
            ->values();

        $categorias = CategoriaEmergencia::with('parent')
            ->whereNotNull('parent_id')
            ->orderBy('parent_id')
            ->orderBy('nombre')
            ->get()
            ->map(function ($categoria) {
                return [
                    'id' => $categoria->id,
                    'nombre' => $categoria->parent->nombre . ' - ' . $categoria->nombre,
                ];
            });

        $provincias = Provincia::select('id','nombre')
            ->orderBy('codigo')
            ->get();

        $personales = Personal::with('titulo')
            ->whereHas('user.roles', function($query) {
                $query->where('rol_id', 6);
            })
            ->where('estado', 'ACTIVO')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'nombre' => strtoupper($p->nombre)
            ]);

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

        $insumos = Insumo::where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'presentacion' => $p->presentacion
            ]);

        $personalesParte = $parteEmergencia->personales
            ->pluck('id')
            ->values();

        $personalesQapParte = $parteEmergencia->personalesQap
            ->map(fn ($p) => [
                'personal_id' => $p->id,
                'nombre_personal' => $p->pivot->nombre_personal,
                'desde' => $p->pivot->desde,
                'hasta' => $p->pivot->hasta,
                'tiempo_total' => $p->pivot->tiempo_total,
            ])
            ->values();

        $insumosParte = $parteEmergencia->insumos
            ->map(fn ($i) => [
                'insumo_id' => $i->id,
                'cantidad' => $i->pivot->cantidad,
            ])
            ->values();

        return inertia('partesEmergencias/edit', [
            'categorias' => $categorias,
            'provincias' => $provincias,
            'personales' => $personales,
            'personalesPuestos' => $personalesPuestos,
            'insumos' => $insumos,
            'parteEmergencia' => $parteEmergencia, 
            'categoriasParte' => $categoriasParte,           
            'personalesParte' => $personalesParte,
            'personalesQapParte' => $personalesQapParte,
            'insumosParte' => $insumosParte,
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => (int) $request->input('year', now()->year),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ValidarParteEmergenciaRequest $request, ParteEmergencia $parteEmergencia, MapaService $mapaService)
    {
        $personalPuesto = PersonalPuesto::with(['personal.titulo','puesto'])
            ->where('personal_id', Auth::user()->personal->id)
            ->where('predeterminado', 1)
            ->firstOrFail();       

        /*$personalPuestoRevisa = PersonalPuesto::with(['personal.titulo','puesto'])
            ->findOrFail($request->personal_puesto_revisa_id);*/

        $formato = Numeracion::where('nombre', 'PARTE EMERGENCIAS')
        ->value('formato');

        // 🧾 Construir número
        $numero = $formato . '-' . str_pad($request->secuencia, 7, '0', STR_PAD_LEFT) . '-' . now()->year;

        $firmantes = $parteEmergencia->firmantes()->count();

        $validated = $request->validated();        

        // Agregar campos automáticos  
        $validated['personal_id'] = Auth::user()->personal->id;
        $validated['personal_puesto_id'] = $personalPuesto->id;   
        $validated['nombre_personal'] = strtoupper($personalPuesto->personal->nombre);
        $validated['puesto_personal'] = strtoupper($personalPuesto->puesto->nombre);
        /*$validated['nombre_revisa'] = strtoupper($personalPuestoRevisa->personal->nombre);
        $validated['puesto_revisa'] = strtoupper($personalPuestoRevisa->puesto->nombre);*/
        $validated['nombre_radio'] = strtoupper(
            Personal::findOrFail($validated['personal_radio_id'])->nombre
        );
        $validated['numero'] = $numero;
        $validated['secuencia'] = $request->secuencia;
        $validated['estado_firma'] = $firmantes > 0 ? 'PENDIENTE' : 'SIN_FIRMA';

        if (!empty($validated['latitud']) && !empty($validated['longitud'])) {
            // Eliminar mapa anterior si existe
            if ($parteEmergencia->ruta_mapa) {
                $rutaAnterior = str_replace('storage/', '', $parteEmergencia->ruta_mapa);

                if (Storage::disk('public')->exists($rutaAnterior)) {
                    Storage::disk('public')->delete($rutaAnterior);
                }
            }

            // generar nuevo mapa
            $rutaMapa = $mapaService->generarMapa(
                $validated['latitud'],
                $validated['longitud']
            );
            $validated['ruta_mapa'] = $rutaMapa;
        }

        $parteEmergencia->update($validated);

        // Eliminar categorías actuales
        ParteEmergenciaCategoria::where(
            'parte_emergencia_id',
            $parteEmergencia->id
        )->delete();

        // Obtener categorías seleccionadas
        $categorias = CategoriaEmergencia::with('parent')
            ->whereIn('id', $validated['categoria_emergencia_id'])
            ->get(['id', 'parent_id', 'nombre']);

        $dataInsert = $categorias->map(function ($categoria) use ($parteEmergencia) {
            return [
                'parte_emergencia_id' => $parteEmergencia->id,
                'categoria_emergencia_id' => $categoria->id,
                'categoria1' => optional($categoria->parent)->nombre,
                'categoria2' => $categoria->nombre,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        ParteEmergenciaCategoria::insert($dataInsert->toArray());

        $personales = Personal::whereIn('id', $validated['personalesParte'] ?? [])
            ->get()
            ->mapWithKeys(fn ($p) => [
                $p->id => [
                    'nombre_personal' => strtoupper($p->nombre)
                ]
            ])
            ->toArray();

        $parteEmergencia->personales()->sync($personales);

        $personalesQapData = collect($validated['personalesQapParte'] ?? []);

        $personales = Personal::with('titulo')
            ->whereIn('id', $personalesQapData->pluck('personal_id'))
            ->get()
            ->pluck('nombre', 'id');

        $parteEmergencia->personalesQap()->sync(
            $personalesQapData->mapWithKeys(function ($item) use ($personales) {
                return [
                    $item['personal_id'] => [
                        'nombre_personal' => strtoupper($personales[$item['personal_id']]) ?? null,
                        'desde' => $item['desde'],
                        'hasta' => $item['hasta'],
                        'tiempo_total' => $item['tiempo_total'],
                    ]
                ];
            })->toArray()
        );

        $parteEmergencia->insumos()->sync(
            collect($validated['insumosParte'] ?? [])
                ->mapWithKeys(fn ($item) => [
                    $item['insumo_id'] => [
                        'cantidad' => $item['cantidad'],
                    ]
                ])
                ->toArray()
        );

        return redirect()->route('partesemergencias.index', [
           'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
        ])->with('success', 'Parte de emergencia actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ParteEmergencia $parteEmergencia)
    {
        //
    }

    public function firmar($id)
    {
        $parteEmergencia = ParteEmergencia::findOrFail($id);

        GenerarPdfParteEmergenciaJob::dispatch($parteEmergencia->id);
    
        return back()->with('success', 'El documento se está firmando en segundo plano');
    }

    public function descargar($id)
    {
        $parteEmergencia = ParteEmergencia::findOrFail($id);
        
        if ($parteEmergencia->ruta_pdf_firmado != null){
            $path = storage_path('app/private/' . $parteEmergencia->ruta_pdf_firmado);

            if (!file_exists($path)) {
                return back()->with('error', 'Archivo no encontrado');
            }

            return response()->download(
                $path,
                "Parte_Emergencia_{$parteEmergencia->numero}.pdf"
            );
        }else{  
            $institucion = Institucion::first();
            $plantilla = Plantilla::find($parteEmergencia->plantilla_id);
            $vista = 'pdf.' . $plantilla->vista_blade;

            $pdf = Pdf::loadView($vista, compact('institucion','parteEmergencia'));

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

            return $pdf->download("Parte_Emergencia_{$parteEmergencia->numero}.pdf");     
        }
    }    
}
