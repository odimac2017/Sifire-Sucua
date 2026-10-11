<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidarInsumoRequest extends FormRequest
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
        $insumoId = $this->insumo?->id;
        
        return [
            'codigo' => [
                'required',
                'string',
                'max:10',
                Rule::unique('insumos', 'codigo')->ignore($insumoId),
            ],
            'nombre' => [
                'required',
                'string',
                'max:300',
                Rule::unique('insumos', 'nombre')->ignore($insumoId),
            ],
            'presentacion' => ['required', 'string', 'max:30']
        ];
    }

    public function attributes()
    {
        return [
            'codigo' => 'código',
            'presentacion' => 'presentación',
        ];
    }
}
