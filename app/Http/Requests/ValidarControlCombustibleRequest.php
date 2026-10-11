<?php

namespace App\Http\Requests;

use App\Models\Bien;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidarControlCombustibleRequest extends FormRequest
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
        $bien = Bien::find($this->bien_id);

        return [
            'bien_id' => ['required','exists:bienes,id'],            
            'personal_id' => ['required', 'exists:personal,id'],
            'combustible_id' => ['required', 'exists:combustibles,id'],
            'fecha' => ['required', 'date'],
            'km_recorrido' => Rule::when(
                $bien && $bien->tipo_bien === 'V',
                ['required', 'numeric', 'min:1'],
                ['nullable']
            ),
            'cantidad_galones' => ['required','numeric','min:1'],
            'precio_galon' => ['required','numeric','min:1'],
        ];
    }
  
    public function attributes(): array
    {
        return [
            'bien_id' => 'vehículo o equipo',
            'personal_id' => 'chofer asignado',            
            'combustible_id' => 'tipo de combustible',
            'km_recorrido' => 'kilometraje de recorrido',
            'cantidad_galones' => 'cantidad galones',
            'precio_galon' => 'precio x galón',
        ];
    }

    public function messages(): array
    {
        return [
            'km_recorrido.min' => 'El :attribute debe ser mínimo 1.',
            'cantidad_galones.min' => 'El :attribute debe ser mínimo 1.',
            'precio_galon.min' => 'El :attribute debe ser mínimo 1.',
        ];
    }
}
