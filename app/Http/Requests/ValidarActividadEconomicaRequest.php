<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidarActividadEconomicaRequest extends FormRequest
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
        $actividadId = $this->actividadEconomica?->id;
        
        return [
            'provincia_id' => ['required', 'exists:provincias,id'],
            'canton_id' => ['required', 'exists:cantones,id'],
            'parroquia_id' => ['required', 'exists:parroquias,id'],
            'cliente_id' => ['required', 'exists:clientes,id'],
            'tipo_actividad' => ['required', 'in:E,V'],
            'fecha_inicio' => ['required', 'date', 'before_or_equal:today'],
            
            'e_nombre_comercial' => ['required_if:tipo_actividad,E', 'nullable', 'string', 'max:300'],
            'e_actividad_principal' => ['required_if:tipo_actividad,E', 'nullable', 'string', 'max:300'],
            'e_actividad_secundaria' => ['nullable', 'string', 'max:200'],
            'e_clave_catastral' => [Rule::unique('actividades_economicas', 'e_clave_catastral')->ignore($actividadId),'nullable', 'string', 'max:20'],
            'e_sector' => ['nullable', 'string', 'max:100'],
            'e_direccion' => ['required_if:tipo_actividad,E', 'nullable', 'string', 'max:300'],
            'e_telefonos' => ['nullable', 'string', 'max:30'],
            'e_latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'e_longitud' => ['nullable', 'numeric', 'between:-180,180'],

            'v_compania_id' => ['nullable', 'exists:clientes,id'],
            'v_placa' => ['required_if:tipo_actividad,V', 'nullable', 'string', 'max:30'],
            'v_marca' => ['required_if:tipo_actividad,V', 'nullable', 'string', 'max:30'],
            'v_clase' => ['nullable', 'string', 'max:30'],                     
            'v_color' => ['nullable', 'string', 'max:30'],
            'v_tipo' => ['nullable', 'string', 'max:30'],   
            'v_cilindraje' => ['nullable', 'integer', 'between:0,100000'],
            'v_combustible' => ['nullable', 'string', 'in:GASOLINA,DIESEL'], 
            'v_numero_unidad' => ['nullable', 'string', 'max:5'], 
        ];
    }

    public function attributes()
    {
        return [
            'provincia_id' => 'provincia',
            'canton_id' => 'cantón',
            'parroquia_id' => 'parroquia',
            'tipo_actividad' => 'tipo de actividad',
            'fecha_inicio' => 'fecha de inicio',
            'e_nombre_comercial' => 'nombre comercial',
            'e_actividad_principal' => 'actividad principal',
            'e_actividad_secundaria' => 'actividad secundaria',
            'e_sector' => 'sector',
            'e_direccion' => 'dirección',
            'e_telefonos' => 'telefonos',
            'e_latitud' => 'latitud',
            'e_longitud' => 'longitud',
            'v_compania_id' => 'compañía de transportes',
            'v_placa' => 'placa',
            'v_marca' => 'marca',            
            'v_clase' => 'clase',
            'v_color' => 'color',
            'v_tipo' => 'tipo',
            'v_cilindraje' => 'cilindraje',
            'v_combustible' => 'combustible',
            'v_numero_unidad' => 'número de unidad',
        ];
    }
}
