<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PermisoRequest extends FormRequest
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
            'id_publicacion' => 'required|integer',
            'id_usuario' => 'nullable|integer',
            'id_rol' => 'nullable|integer',
            'id_sedereg' => 'nullable|integer',
            'id_sedejuris' => 'nullable|integer',
            'estado' => 'required|integer'
        ];
    }

    public function messages()
    {
        return [
            'id_publicacion.integer' => 'El :attribute debe ser un número entero.',
            'id_usuario.integer' => 'El :attribute debe ser un número entero.',
            'id_rol.integer' => 'El :attribute debe ser un número entero.',
            'id_sedereg.integer' => 'El :attribute debe ser un número entero.',
            'id_sedejuris.integer' => 'El :attribute debe ser un número entero.',
            'estado.integer' => 'El :attribute debe ser un número entero.',
        ];
    }

    public function attributes()
    {
        return [
            'id_publicacion' => 'publicación',
            'id_usuario' => 'usuario',
            'id_rol' => 'rol',
            'id_sedereg' => 'sede registro',
            'id_sedejuris' => 'sede jurisdiccional',
            'estado' => 'estado'
        ];
    }




}
