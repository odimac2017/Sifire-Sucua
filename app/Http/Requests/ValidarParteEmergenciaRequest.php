<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidarParteEmergenciaRequest extends FormRequest
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
            'categoria_emergencia_id' => ['required', 'array'],
            'categoria_emergencia_id.*' => ['exists:categorias_emergencias,id'],
            'provincia_id' => 'required|exists:provincias,id',
            'canton_id' => 'required|exists:cantones,id',
            'parroquia_id' => 'required|exists:parroquias,id',
            //'personal_puesto_revisa_id' => 'required|exists:personal_puestos,id',
            'personal_radio_id' => 'required|exists:personal,id',
            'inicial' => 'boolean',
            'seguimiento' => 'boolean',
            'final' => 'boolean',
            'fecha_llamada' => 'required|date',
            'fecha_llegada' => 'required|date|after_or_equal:fecha_llamada',
            'fecha_atencion' => 'required|date|after_or_equal:fecha_llegada',
            'direccion' => 'required|string|max:300',
            'fuente' => 'nullable|string|max:200',
            'contexto_general' => 'required|string|max:100000',
            'personal_externo' => 'nullable|string|max:300',            
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'personalesParte' => ['nullable', 'array'],
            'personalesParte.*' => ['integer', 'exists:personal,id'],
            'personalesQapParte' => ['nullable', 'array'],
            'personalesQapParte.*.personal_id' => ['required', 'integer', 'exists:personal,id'],
            'personalesQapParte.*.desde' => ['required', 'date'],
            'personalesQapParte.*.hasta' => ['required', 'date', 'after:personalesQapParte.*.desde'],
            'personalesQapParte.*.tiempo_total' => ['required', 'string'],
            'insumosParte' => ['nullable', 'array'],
            'insumosParte.*.insumo_id' => ['required', 'integer', 'exists:insumos,id'],
            'insumosParte.*.cantidad' => ['required', 'integer', 'min:1'],           
        ];
    }

    public function attributes()
    {
        return [
            'categoria_emergencia_id' => 'categorías',
            'provincia_id' => 'provincia',
            'canton_id' => 'canton',
            'parroquia_id' => 'parroquia',
            //'personal_puesto_revisa_id' => 'personal que revisa',
            'personal_radio_id' => 'radio operador',            
            'fecha_llamada' => 'fecha de llamada',
            'fecha_llegada' => 'fecha de llegada',
            'fecha_atencion' => 'fecha de atención',
            'direccion' => 'dirección',
            'contexto_general' => 'contexto general',
            'personal_externo' => 'personal externo',
        ];
    }
}
