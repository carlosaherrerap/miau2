<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VistapRequest extends FormRequest
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
            'id_publicacion' => 'required|integer',
            'fecha_vista' => 'required|date_format:Y-m-d H:i:s'
        ];
    }

    public function messages()
    {
        return [
            'id_usuario.required' => 'El usuario es requerido',
            'id_usuario.integer' => 'El usuario debe ser un entero',
            'id_publicacion.required' => 'La publicacion es requerida',
            'id_publicacion.integer' => 'La publicacion debe ser un entero',
            'fecha_vista.required' => 'La fecha de vista es requerida',
            'fecha_vista.date_format' => 'La fecha de vista debe ser una fecha'
        ];
    }

    public function attributes()
    {
        return [
            'id_usuario' => 'usuario',
            'id_publicacion' => 'publicacion',
            'fecha_vista' => 'fecha de vista'
        ];
    }
}
