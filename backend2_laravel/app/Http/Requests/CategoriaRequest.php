<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CategoriaRequest extends FormRequest
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
            'nombre' => 'required|string',
            'tipo' => 'required|string'
        ];
    }


    public function messages()
    {
        return [
            'nombre.required' => 'El :attribute es obligatorio.',
            'nombre.string' => 'El :attribute debe ser texto.',
            'tipo.required' => 'El :attribute es obligatorio.',
            'tipo.string' => 'El :attribute debe ser texto.',
        ];
    }

    public function attributes()
    {
        return [
            'nombre' => 'nombre de la categoría',
            'tipo' => 'tipo de categoría'
        ];
    }


}
