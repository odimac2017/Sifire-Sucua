<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ValidarParteConsignaRequest;
use App\Models\Bien;
use App\Models\FuncionConsigna;
use App\Models\Institucion;
use App\Models\ParteConsigna;
use App\Models\ParteConsignaConsigna;
use App\Models\ParteConsignaIntegrante;
use App\Models\ParteConsignaPlanificacion;
use App\Models\ParteConsignaReporteQap;
use App\Models\ParteConsignaVehiculo;
use App\Models\Personal;
use App\Models\PersonalPuesto;
use App\Models\Plantilla;
use App\Models\TurnoGuardia;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ParteConsignaController extends Controller
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

        $partesConsignas = ParteConsigna::with([
            'turnoGuardia',
            'consignas'
        ])
        ->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('novedades', 'like', "%{$search}%");
            });
        })
        ->whereYear('fecha', $year)
        ->orderByDesc('fecha')
        ->paginate(10)
        ->withQueryString();
        
        return inertia('partesConsignas/index', [
            'partesConsignas' => $partesConsignas,
            'auth_personal_id' => Auth::user()->personal?->id ?? null,
            'filters' => [
                'search' => $search,
                'year' => $year,
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, ParteConsigna $parteConsigna)
    {        
        $parteConsigna->load([
            'turnoGuardia',
            'consignas'
        ]);

        return inertia('partesEmergencias/show', [
            'parteConsigna' => $parteConsigna,
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => (int) $request->input('year', now()->year),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, ParteConsigna $parteConsigna)
    {
        // 👇 VALIDACIÓN DE SEGURIDAD
        if ($parteConsigna->personal_entrega_id != Auth::user()->personal->id) {
            abort(403, 'No autorizado para editar este parte de consignas.');
        }        

        $turnosGuardias = TurnoGuardia::where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'nombre' => strtoupper($p->nombre),
                'nombre_oficial' => strtoupper($p->nombre_oficial)
            ]);

        $personales = Personal::with('titulo')
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

        $integrantesParte = ParteConsignaIntegrante::where('parte_consigna_id', $parteConsigna->id)
            ->get()
            ->keyBy('funcion_consigna_id');

        $funciones = FuncionConsigna::where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get()
            ->map(function ($f) use ($integrantesParte) {
                $integrante = $integrantesParte->get($f->id);
                return [
                    'id' => $integrante->id ?? null,
                    'funcion_consigna_id' => $f->id,
                    'nombre' => $f->nombre,
                    'personal_id' => $integrante->personal_id ?? null,
                ];
            });
        
        $vehiculosParte = ParteConsignaVehiculo::where('parte_consigna_id', $parteConsigna->id)
            ->get()
            ->keyBy('bien_id');

        $vehiculos = Bien::where('tipo_bien', 'V')
            ->where('interviene_emergencia', 1)
            ->where('estado', '!=', 'BAJA')
            ->orderBy('tipo_vehiculo')
            ->orderBy('identificacion')
            ->get()
            ->map(function ($f) use ($vehiculosParte) {
                $vehiculo = $vehiculosParte->get($f->id);
                return [
                    'id' =>$vehiculo->id ?? null,
                    'bien_id' => $f->id,
                    'tipo_vehiculo' => $f->tipo_vehiculo,
                    'marca' => $f->marca,
                    'identificacion' => $f->identificacion,
                    'operativo' => $vehiculo->operativo,
                    'motivo' => $vehiculo ? $vehiculo->motivo : '',
                ];
            });

        $reportesQap = $parteConsigna->reportesQap
            ->map(fn ($p) => [
                'id' => $p->pivot->id,
                'personal_id' => $p->id,
                'hora' => $p->pivot->hora,
                'reporte' => $p->pivot->reporte,
            ])
            ->values();
        
        $consignas = $parteConsigna->consignas
            ->map(fn ($p) => [
                'id' => $p->id,
                'personal_id' => $p->personal_id,
                'consigna' => $p->consigna,
                'cumple' => $p->cumple,
                'motivo' => $p->motivo
            ])
            ->values();
        
        $planificaciones = $parteConsigna->planificaciones
            ->map(fn ($p) => [     
                'id' => $p->pivot->id,           
                'personal_id' => $p->id,                
                'actividad' => $p->pivot->actividad,
                'cumple' => $p->pivot->cumple,
                'motivo' => $p->pivot->motivo
            ])
            ->values();
        
        $parteConsigna->load([
            'turnoGuardia'
        ]);

        return inertia('partesConsignas/edit', [
            'turnosGuardias' => $turnosGuardias,            
            'personales' => $personales,
            'personalesPuestos' => $personalesPuestos,
            'funciones' => $funciones,
            'vehiculos' => $vehiculos,
            'reportesQap' => $reportesQap,
            'consignas' => $consignas,
            'planificaciones' => $planificaciones,
            'parteConsigna' => $parteConsigna,
            'fecha_siguiente_turno' => Carbon::parse($parteConsigna->fecha)->addDay()->format('Y-m-d'),
            'filters' => [
                'search' => trim($request->input('search', '')),
                'year' => (int) $request->input('year', now()->year),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ValidarParteConsignaRequest $request, ParteConsigna $parteConsigna)
    {       
        $personalPuesto = PersonalPuesto::with(['personal.titulo','puesto'])
            ->where('personal_id', $request->personal_entrega_id)
            ->where('predeterminado', 1)
            ->firstOrFail();      

        $personalPuestoRevisa = PersonalPuesto::with(['personal.titulo','puesto'])
            ->findOrFail($request->personal_puesto_revisa_id);

        $validated = $request->validated();        

        // Agregar campos automáticos  
        $validated['nombre_entrega'] = strtoupper($personalPuesto->personal->nombre);
        $validated['puesto_entrega'] = strtoupper($personalPuesto->puesto->nombre);
        $validated['nombre_revisa'] = strtoupper($personalPuestoRevisa->personal->nombre);
        $validated['puesto_revisa'] = strtoupper($personalPuestoRevisa->puesto->nombre);
        
        $parteConsigna->update($validated);

        $personales = Personal::whereIn(
            'id',
            collect($request->integrantes)
                ->pluck('personal_id')
                ->filter()
        )->get()->keyBy('id');

        foreach ($request->integrantes as $item) {
            ParteConsignaIntegrante::updateOrCreate(
                ['id' => $item['id']],
                [
                    'parte_consigna_id' => $parteConsigna->id,
                    'funcion_consigna_id' => $item['funcion_consigna_id'],
                    'personal_id' => $item['personal_id'],
                    'nombre_funcion' => $item['nombre'],
                    'nombre_personal' => optional(
                        $personales->get($item['personal_id'])
                    )->nombre
                        ? strtoupper($personales->get($item['personal_id'])->nombre)
                        : null,
                ]
            );
        }

        foreach ($request->vehiculos as $item) {
            ParteConsignaVehiculo::updateOrCreate(
                [
                    'id' => $item['id']
                ],
                [                    
                    'operativo' => $item['operativo'],
                    'motivo' => $item['motivo'] ?? null,
                ]
            );
        }

        $reportesQap = collect($request->reportesQap)
            ->pluck('id')
            ->filter()
            ->toArray();

        ParteConsignaReporteQap::where('parte_consigna_id', $parteConsigna->id)
            ->when(count($reportesQap) > 0, function ($q) use ($reportesQap) {
                $q->whereNotIn('id', $reportesQap);
            })
            ->delete();

        $personales = Personal::whereIn(
            'id',
            collect($request->reportesQap)->pluck('personal_id')
        )->get()->keyBy('id');

        foreach ($request->reportesQap as $item) {
            ParteConsignaReporteQap::updateOrCreate(
                [
                    'id' => $item['id'] ?? null
                ],
                [
                    'parte_consigna_id' => $parteConsigna->id,
                    'personal_id' => $item['personal_id'],
                    'hora' => $item['hora'],
                    'reporte' => $item['reporte'] ?? null,
                    'nombre_personal' => strtoupper($personales[$item['personal_id']]?->nombre),
                ]
            );
        }

        $consignas = collect($request->consignas)
            ->pluck('id')
            ->filter()
            ->toArray();

        ParteConsignaConsigna::where('parte_consigna_id', $parteConsigna->id)
            ->when(count($consignas) > 0, function ($q) use ($consignas) {
                $q->whereNotIn('id', $consignas);
            })
            ->delete();

        $personales = Personal::whereIn(
            'id',
            collect($request->consignas)->pluck('personal_id')
        )->get()->keyBy('id');

        foreach ($request->consignas as $item) {
            ParteConsignaConsigna::updateOrCreate(
                [
                    'id' => $item['id'] ?? null
                ],
                [
                    'parte_consigna_id' => $parteConsigna->id,
                    'personal_id' => $item['personal_id'],
                    'consigna' => $item['consigna'] ?? '',
                    'cumple' => $item['cumple'],
                    'motivo' => $item['motivo'] ?? '',
                    'nombre_personal' => strtoupper($personales[$item['personal_id']]?->nombre),
                ]
            );
        }

        $planificaciones = collect($request->planificaciones)
            ->pluck('id')
            ->filter()
            ->toArray();

        ParteConsignaPlanificacion::where('parte_consigna_id', $parteConsigna->id)
            ->when(count($planificaciones) > 0, function ($q) use ($planificaciones) {
                $q->whereNotIn('id', $planificaciones);
            })
            ->delete();

        $personales = Personal::whereIn(
            'id',
            collect($request->planificaciones)->pluck('personal_id')
        )->get()->keyBy('id');

        foreach ($request->planificaciones as $item) {
            ParteConsignaPlanificacion::updateOrCreate(
                [
                    'id' => $item['id'] ?? null
                ],
                [
                    'parte_consigna_id' => $parteConsigna->id,
                    'personal_id' => $item['personal_id'],
                    'actividad' => $item['actividad'] ?? '',
                    'cumple' => $item['cumple'],
                    'motivo' => $item['motivo'] ?? '',
                    'nombre_personal' => strtoupper($personales[$item['personal_id']]?->nombre),
                ]
            );
        }
        
        return redirect()->route('partesconsignas.index', [
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
        ])->with('success', 'Parte diario de consignas actualizado correctamente');       
    }

    public function close(Request $request, ParteConsigna $parteConsigna)
    {   
        $plantillaId = Plantilla::where('nombre', 'PARTE DIARIO CONSIGNAS')
        ->where('predeterminado', 1)
        ->value('id');

        DB::transaction(function () use ($request, $plantillaId, $parteConsigna) {
            // 1. CERRAR TURNO ACTUAL
            $parteConsigna->update([
                'estado' => 'CERRADO',
            ]);

            // 2. CREAR NUEVO TURNO SEGÚN CONDICIÓN
            if ($request->continua_guardia) {
                $nuevo = ParteConsigna::create([
                    'plantilla_id' => $plantillaId,
                    'turno_guardia_id' => $parteConsigna->turno_guardia_id,
                    'turno_guardia_recibe_id' => $parteConsigna->turno_guardia_id,
                    'personal_entrega_id' => $parteConsigna->personal_entrega_id,
                    'personal_recibe_id' => $parteConsigna->personal_entrega_id,
                    'personal_puesto_revisa_id' => $parteConsigna->personal_puesto_revisa_id,
                    'nombre_entrega' => $parteConsigna->nombre_entrega,
                    'puesto_entrega' => $parteConsigna->puesto_entrega,
                    'nombre_recibe' => $parteConsigna->nombre_entrega,
                    'nombre_revisa' => $parteConsigna->nombre_revisa,
                    'puesto_revisa' => $parteConsigna->puesto_revisa,
                    'fecha' => $request->fecha_siguiente_turno,
                    'estado' => 'ABIERTO',           
                ]);

                foreach ($parteConsigna->integrantes as $i) {
                    $nuevo->integrantes()->create([
                        'funcion_consigna_id' => $i->funcion_consigna_id,
                        'personal_id' => $i->personal_id,
                        'nombre_personal' => $i->nombre_personal,
                        'nombre_funcion' => $i->nombre_funcion,                
                    ]);
                }

                $this->copiarDatos($parteConsigna, $nuevo);

            } else {

                $turnoRecibe = TurnoGuardia::with('personalPuesto')
                    ->findOrFail($request->turno_guardia_recibe_id);

                $nuevo = ParteConsigna::create([
                    'plantilla_id' => $plantillaId,
                    'turno_guardia_id' => $request->turno_guardia_recibe_id,
                    'personal_entrega_id' => $turnoRecibe->personalPuesto->personal_id,
                    'personal_puesto_revisa_id' => $parteConsigna->personal_puesto_revisa_id,
                    'nombre_entrega' => $turnoRecibe->nombre_oficial,
                    'puesto_entrega' => $turnoRecibe->puesto_oficial,
                    'nombre_revisa' => $parteConsigna->nombre_revisa,
                    'puesto_revisa' => $parteConsigna->puesto_revisa,
                    'fecha' => $request->fecha_siguiente_turno,
                    'estado' => 'ABIERTO',
                ]);

                $this->copiarDatos($parteConsigna, $nuevo);
            }            
        });

        return redirect()->route('partesconsignas.index', [
            'search' => trim($request->input('search', '')),
            'year' => (int) $request->input('year', now()->year),
        ])->with('success', 'Parte diario de consignas cerrado correctamente');   
    }

    private function copiarDatos($old, $new)
    {
        foreach ($old->vehiculos as $v) {
            $new->vehiculos()->create([
                'bien_id' => $v->bien_id,
                'operativo' => $v->operativo,
                'motivo' => $v->motivo,
            ]);
        }

        foreach ($old->consignas as $r) {
            if ($r->cumple == 0){
                $new->consignas()->create([
                    'personal_id' => $r->personal_id,
                    'nombre_personal' => $r->nombre_personal,
                    'consigna' => $r->consigna,
                    'cumple' => $r->cumple,
                    'motivo' => $r->motivo,                
                ]);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ParteConsigna $parteConsigna)
    {
        if ($parteConsigna->estado !== 'ABIERTO') {
            return back()->withErrors([
                'error' => 'Solo se puede eliminar un parte diario de consignas en estado ABIERTO.'
            ]);
        }

        DB::transaction(function () use ($parteConsigna) {

            $fecha = \Carbon\Carbon::parse($parteConsigna->fecha);

            // 1. eliminar relaciones
            $parteConsigna->integrantes()->delete();
            $parteConsigna->vehiculos()->delete();
            $parteConsigna->reportesQap()->delete();
            $parteConsigna->consignas()->delete();
            $parteConsigna->planificaciones()->delete();

            // 2. eliminar parte
            $parteConsigna->delete();

            // 3. buscar el parte del día anterior (si existe)
            $parteAnterior = ParteConsigna::whereDate('fecha', $fecha->copy()->subDay())
                ->latest('id')
                ->first();

            // 4. reabrirlo si existe
            if ($parteAnterior) {
                $parteAnterior->update([
                    'estado' => 'ABIERTO'
                ]);
            }
        });

        return redirect()->route('partesconsignas.index')
            ->with('success', 'Parte diario de consignas eliminado y reactivado el anterior');
    }

    public function descargar($id)
    {
        $parteConsigna = ParteConsigna::findOrFail($id);

        $fecha = Carbon::parse($parteConsigna->fecha);        
        
        $institucion = Institucion::first();
        $plantilla = Plantilla::find($parteConsigna->plantilla_id);
        $vista = 'pdf.' . $plantilla->vista_blade;

        $pdf = Pdf::loadView($vista, compact('institucion','parteConsigna'));

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

        return $pdf->download("Parte_Consignas_{$fecha->year}.pdf");
    }    
}
