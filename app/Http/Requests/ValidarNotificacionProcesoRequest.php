<?php

namespace App\Http\Requests;

use App\Models\ProcesoPermiso;
use App\Models\SolicitudInspeccion;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidarNotificacionProcesoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cliente_id' => ['required','exists:clientes,id'],
            'actividad_economica_id' => [
                'required',
                'exists:actividades_economicas,id',
                function ($attribute, $value, $fail) {
                    if (!$value) return;

                    // 👉 EXCLUIR en caso de update
                    $excludeId = $this->route('solicitudInspeccion')?->id;

                    $year = Carbon::now()->year;

                    /*
                    |--------------------------------------------------------------------------
                    | 1. VALIDAR SOLICITUD GENERADA
                    |--------------------------------------------------------------------------
                    */
                    $querySolicitud = SolicitudInspeccion::where('estado', 'GENERADO')
                        ->whereHas('procesoPermiso', function ($q) use ($value) {
                            $q->where('actividad_economica_id', $value);
                        })
                        ->whereYear('created_at', $year);

                    if ($excludeId) {
                        $querySolicitud->where('id', '!=', $excludeId);
                    }

                    if ($querySolicitud->exists()) {
                        $fail('Ya existe una solicitud de inspección generada para esta actividad económica.');
                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | 2. VALIDAR PERMISO EN EL MISMO AÑO
                    |--------------------------------------------------------------------------
                    */
                   
                    $queryPermiso = SolicitudInspeccion::whereHas('procesoPermiso', function ($q) use ($value, $year) {
                        $q->where('actividad_economica_id', $value)
                        ->whereNotNull('finished_at')
                        ->whereYear('finished_at', $year);
                    });

                    if ($excludeId) {
                        $queryPermiso->where('id', '!=', $excludeId);
                    }

                    if ($queryPermiso->exists()) {
                        $fail('Ya existe un permiso de funcionamiento para esta actividad económica en el año actual.');
                    }
                }
            ],
            'tipo_permiso_id' => ['required','exists:tipos_permisos,id'],
            'tarifario_id' => ['required', 'array'],
            'tarifario_id.*' => ['exists:tarifarios,id'],
            'fecha_inspeccion' => ['required', 'date', 'after_or_equal:today'],
            'valor_impuesto_predial' => ['required','numeric','min:0'],
            'artesano_calificado' => ['required', 'numeric', 'in:0,1'],
            'porcentaje_descuento' => ['required', 'numeric', 'in:0,100'],
            'fecha_inicio' => ['nullable', 'date', 'before_or_equal:today'], 
            'personal_id' => ['required','exists:personal,id'],
            'documento_representante_legal' => [
                Rule::requiredIf($this->tipo_cliente_id == 2),
                'nullable',
                'string',
                'max:20'
            ],
            'nombre_representante_legal' => [
                Rule::requiredIf($this->tipo_cliente_id == 2),
                'nullable',
                'string',
                'max:100'
            ],
            'documento_persona_recibe' => ['nullable','string','max:20'],
            'nombre_persona_recibe' => ['nullable','string','max:100'],
            'observaciones' => ['nullable','string','max:300'],
        ];
    }

    public function attributes(): array
    {
        return [
            'actividad_economica_id' => 'actividad económica',
            'tipo_permiso_id' => 'tipo de permiso',
            'tarifario_id' => 'tarifarios',
            'personal_id' => 'inspector asignado',
            'fecha_inspeccion' => 'fecha para inspección',
            'valor_impuesto_predial' => 'valor del impuesto predial',
            'fecha_inicio' => 'fecha de inicio',
            'documento_representante_legal' => 'documento del representante legal',
            'nombre_representante_legal' => 'nombre del representante legal',
            'documento_persona_recibe' => 'documento de la persona que recibe la boleta',
            'nombre_persona_recibe' => 'nombre de la persona que recibe la boleta',
        ];
    }
}
