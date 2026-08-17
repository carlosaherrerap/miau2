<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
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
            'cod_ticket' => 'required|string',
            'id_usuario' => 'required|integer',
            'fecha_emision' => 'required|date',
            'id_categoria' => 'required|integer',
            'descripcion_problema' => 'required|string',
            'nivel_importancia' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'cod_ticket.required' => 'El codigo del ticket es requerido.',
            'cod_ticket.string' => 'El codigo del ticket debe ser una cadena de texto.',
            'id_usuario.required' => 'El usuario es requerido.',
            'id_usuario.integer' => 'El usuario debe ser un entero.',
            'fecha_emision.required' => 'La fecha de emision es requerida.',
            'fecha_emision.date' => 'La fecha de emision debe ser una fecha valida.',
            'id_categoria.required' => 'La categoria es requerida.',
            'id_categoria.integer' => 'La categoria debe ser un entero.',
            'descripcion_problema.required' => 'La descripcion del problema es requerida.',
            'descripcion_problema.string' => 'La descripcion del problema debe ser una cadena de texto.',
            'nivel_importancia.required' => 'El nivel de importancia es requerido.',
            'nivel_importancia.string' => 'El nivel de importancia debe ser una cadena de texto.'
        ];
    }

    public function attributes()
    {
        return [
            'cod_ticket' => 'codigo del ticket',
            'id_usuario' => 'usuario',
            'fecha_emision' => 'fecha de emision',
            'id_categoria' => 'categoria',
            'descripcion_problema' => 'descripcion del problema',
            'nivel_importancia' => 'nivel de importancia'
        ];
    }
}
