<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidarBienRequest extends FormRequest
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
        $bienId = $this->bien?->id;

        return [
            'clasificacion_bien_id' => ['required', 'exists:clasificaciones_bienes,id'],
            'codigo_inventario' => [
                'required',
                'string',
                'max:100',
                Rule::unique('bienes', 'codigo_inventario')->ignore($bienId),
            ],
            'tipo_bien' => ['required', 'in:G,V'],
            'marca' => ['required', 'string', 'max:50'],
            'modelo' => ['required', 'string', 'max:50'],
            'serie' => ['required_if:tipo_bien,G', 'nullable', 'string', 'max:50'],
            'identificacion' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('bienes', 'identificacion')->ignore($bienId),
            ],
            'placa' => ['required_if:tipo_bien,V', 'nullable', 'string', 'max:10'],
            'numero_chasis' => ['required_if:tipo_bien,V', 'nullable', 'string', 'max:30'],
            'numero_motor' => ['required_if:tipo_bien,V', 'nullable', 'string', 'max:30'],
            'cilindraje' => ['required_if:tipo_bien,V', 'nullable', 'string', 'max:30'],
            'anio_modelo' => ['required_if:tipo_bien,V', 'nullable', 'string', 'max:30'],
            'clase_vehiculo' => ['required_if:tipo_bien,V', 'nullable', 'string', 'max:30'],
            'tipo_vehiculo' => ['required_if:tipo_bien,V', 'nullable', 'string', 'max:30'],
            'pais_origen' => ['required_if:tipo_bien,V', 'nullable', 'string', 'max:30'],
            'combustible' => ['required_if:tipo_bien,V', 'nullable', 'string', 'max:30'],
            'color1' => ['required', 'string', 'max:30'],
            'color2' => ['nullable', 'string', 'max:30'],
            'caracteristicas' => ['nullable', 'string', 'max:300'],
            'fecha_ingreso' => ['required', 'date', 'before_or_equal:today'],
            'vida_util' => ['required', 'string'],
            'interviene_emergencia' => ['nullable', 'boolean'],
            'control_combustible' => ['nullable', 'boolean'],
            'observaciones' => ['nullable', 'string', 'max:200'],
            'estado_bien' => ['required', 'string'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function attributes()
    {
        return [
            'clasificacion_bien_id' => 'clasificación del bien',
            'codigo_inventario' => 'código de inventario',
            'identificacion' => 'identificación',
            'numero_chasis' => 'número de chasis',
            'numero_motor' => 'número de motor',
            'anio_modelo' => 'año modelo',
            'clase_vehiculo' => 'clase de vehículo',
            'tipo_vehiculo' => 'tipo de vehículo',
            'pais_origen' => 'país de origen',
            'color1' => 'color 1',
            'color2' => 'color 2',
            'caracteristicas' => 'características',
            'fecha_ingreso' => 'fecha de ingreso',
            'vida_util' => 'vida útil',
            'estado_bien' => 'estado',
        ];
    }
}
