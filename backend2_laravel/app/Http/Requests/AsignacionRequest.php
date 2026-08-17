<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AsignacionRequest extends FormRequest
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
            'id_usuario' => 'required|integer',
            'id_rol' => 'nullable|integer',
            'id_sedereg' => 'nullable|integer',
            'id_sedejuris' => 'nullable|integer'
        ];
    }

    public function messages()
    {
        return [
            'id_usuario.required' => 'El :attribute es obligatorio.',
            'id_usuario.integer' => 'El :attribute debe ser un número entero.',
            'id_rol.required' => 'El :attribute es obligatorio.',
            'id_rol.integer' => 'El :attribute debe ser un número entero.',
            'id_sedereg.required' => 'El :attribute es obligatorio.',
            'id_sedereg.integer' => 'El :attribute debe ser un número entero.',
            'id_sedejuris.required' => 'El :attribute es     obligatorio.',
            'id_sedejuris.integer' => 'El :attribute debe ser un número entero.',
        ];
    }

    public function attributes()
    {
        return [
            'id_usuario' => 'usuario',
            'id_rol' => 'rol',
            'id_sedereg' => 'sede regional',
            'id_sedejuris' => 'sede jurisdiccional'
        ];
    }
}
