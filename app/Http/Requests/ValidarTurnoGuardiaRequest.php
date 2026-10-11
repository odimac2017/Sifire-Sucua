<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidarTurnoGuardiaRequest extends FormRequest
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
        $turnoId = $this->turnoGuardia?->id;

        return [
            'personal_puesto_oficial_id' => 'required|exists:personal_puestos,id',            
            'nombre' => [
                'required',
                'string',
                'max:300',
                Rule::unique('turnos_guardias', 'nombre')->ignore($turnoId)
            ]                  
        ];
    }

    public function attributes()
    {
        return [
            'personal_puesto_oficial_id' => 'oficial de guardia', 
        ];
    }
}
