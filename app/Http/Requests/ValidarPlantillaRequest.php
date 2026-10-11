<?php

namespace App\Http\Requests;

use App\Models\PersonalPlantilla;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidarPlantillaRequest extends FormRequest
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
            'plantilla_id' => ['required','exists:plantillas,id'],
            'personal_id' => ['required','exists:personal,id'],
            'puesto' => ['required','string','max:255'],
            'marcador_firma' => [
                'nullable',
                'string',
                'max:100',
                Rule::requiredIf(fn () => $this->boolean('firma_electronica')),
            ],
            'firma_electronica' => ['boolean'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $plantillaId = $this->plantilla_id;

            // validar máximo 2 firmantes
            $totalFirmantes = PersonalPlantilla::where('plantilla_id', $plantillaId)->count();

            if ($totalFirmantes >= 2) {
                $validator->errors()->add(
                    'personal_id',
                    'Esta plantilla ya tiene el máximo de firmantes.'
                );
            }
        });
    }

    public function attributes(): array
    {
        return [
            'personal_id' => 'personal',
            'marcador_firma' => 'marcador de la firma',
        ];
    }
}
