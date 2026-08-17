<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SedejRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_sedereg' => 'required|integer',
            'nombre' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'id_sedereg.required' => 'El :attribute es requerido.',
            'id_sedereg.integer' => 'El :attribute debe ser un entero.',
            'nombre.required' => 'El :attribute es requerido.',
            'nombre.string' => 'El :attribute debe ser una cadena de texto.'
        ];
    }

    public function attributes()
    {
        return [
            'id_sedereg' => 'ID de sede regional',
            'nombre' => 'nombre'
        ];
    }
}
