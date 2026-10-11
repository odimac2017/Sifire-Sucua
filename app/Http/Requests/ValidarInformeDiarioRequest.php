<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ValidarInformeDiarioRequest extends FormRequest
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
        $informeDiario = $this->route('informeDiario');

        return [
            'personal_puesto_dirige_id' => 'required|exists:personal_puestos,id',
            'fecha_informe' => [
                'required',
                'date',
                Rule::unique('informes_diarios', 'fecha_informe')
                    ->ignore($informeDiario?->id)
                    ->where(function ($query) {
                        return $query->where('personal_id', Auth::user()->personal->id);
                    }),
            ],
            'contenido' => 'required|string|max:100000',
            'inspecciones_aprobadas' => 'required|numeric|min:0',
            'inspecciones_reinspeccion' => 'required|numeric|min:0',
            'inspecciones_no_realizadas' => 'required|numeric|min:0',
            'inspecciones_vehiculares' => 'required|numeric|min:0',
            'notificaciones_realizadas' => 'required|numeric|min:0',
            'total_general' => 'required|numeric',
            'observaciones' => 'required|string|max:100000',
            'despedida' => 'required|string|max:1000',           
        ];
    }

    public function attributes()
    {
        return [
            'personal_puesto_dirige_id' => 'dirigido a',
            'fecha_informe' => 'fecha del informe',
        ];
    }
}
