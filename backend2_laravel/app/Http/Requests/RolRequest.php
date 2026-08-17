<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RolRequest extends FormRequest
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
            'cod' => 'required|string',
            'nombre' => 'required|string'
        ];
    }


    public function messages()
    {
        return [
            'cod.required' => 'El :attribute es requerido.',
            'cod.string' => 'El :attribute debe ser una cadena de texto.',
            'nombre.required' => 'El :attribute es requerido.',
            'nombre.string' => 'El :attribute debe ser una cadena de texto.'
        ];
    }

    public function attributes()
    {
        return [
            'cod' => 'código',
            'nombre' => 'nombre'
        ];
    }

}
