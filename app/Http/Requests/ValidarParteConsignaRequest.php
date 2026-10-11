<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidarParteConsignaRequest extends FormRequest
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
        $baseRules = [
            'continua_guardia' => 'boolean',
            'fecha_siguiente_turno' => 'required|date',
            'action' => 'nullable|string',
        ];
        
        if ($this->action === 'close') {
            return array_merge($baseRules, [
                'turno_guardia_recibe_id' => 'nullable|exists:turnos_guardias,id|required_if:continua_guardia,false',
            ]);
        }

        return array_merge($baseRules, [
            'turno_guardia_id' => 'required|exists:turnos_guardias,id',   
            'personal_entrega_id' => 'required|exists:personal,id',
            'personal_puesto_revisa_id' => 'required|exists:personal_puestos,id',            
            'novedades' => 'required|string|max:10000',
            'integrantes' => ['required', 'array'],
            'integrantes.*.personal_id' => ['nullable','integer','exists:personal,id'],
            'vehiculos' => ['nullable', 'array'],
            'vehiculos.*.bien_id' => ['required', 'integer', 'exists:bienes,id'],
            'vehiculos.*.operativo' => ['boolean'],
            'vehiculos.*.motivo' => ['nullable', 'string'],
            'reportesQap' => ['nullable', 'array'],
            'reportesQap.*.personal_id' => ['required', 'integer', 'exists:personal,id'],
            'reportesQap.*.hora' => ['required'],
            'reportesQap.*.reporte' => ['required', 'string'],
            'consignas' => ['nullable', 'array'],
            'consignas.*.personal_id' => ['required', 'integer', 'exists:personal,id'],
            'consignas.*.consigna' => ['required', 'string'],
            'consignas.*.cumple' => ['boolean'],
            'consignas.*.motivo' => ['nullable', 'string'],
            'planificaciones' => ['nullable', 'array'],
            'planificaciones.*.personal_id' => ['required', 'integer', 'exists:personal,id'],
            'planificaciones.*.actividad' => ['required', 'string'],
            'planificaciones.*.cumple' => ['boolean'],
            'planificaciones.*.motivo' => ['nullable', 'string'],
        ]);
    }

    public function attributes(): array
    {
        return [
            'turno_guardia_recibe_id' => 'turno de guardía que recibe las consignas',    
            'personal_puesto_revisa_id' => 'oficial que revisa el parte',   
            'integrantes.*.personal_id' => 'personal asignado',
        ];
    }

    public function messages(): array
    {
        return [
            'turno_guardia_recibe_id.required_if' => 'El :attribute es obligatorio cuando no continua su guardia.',
        ];
    }
}
