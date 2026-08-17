<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class EstadoRequest extends FormRequest
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
            'id_ticket' => 'required|integer',
            'estado' => 'required|string',
            'fecha_estado_actual' => 'required|date_format:Y-m-d H:i:s',
            'descripcion_solucion' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'id_ticket.required' => 'El :attribute es obligatorio.',
            'id_ticket.integer' => 'El :attribute debe ser un número entero.',
            'estado.required' => 'El :attribute es obligatorio.',
            'estado.string' => 'El :attribute debe ser texto.',
            'fecha_estado_actual.required' => 'La :attribute es obligatoria.',
            'fecha_estado_actual.date_format' => 'La :attribute debe tener un formato de fecha y hora válido.',
            'descripcion_solucion.required' => 'La :attribute es obligatoria.',
            'descripcion_solucion.string' => 'La :attribute debe ser texto.',
        ];
    }

    public function attributes()
    {
        return [
            'id_ticket' => 'ticket',
            'estado' => 'estado',
            'fecha_estado_actual' => 'fecha del estado actual',
            'descripcion_solucion' => 'descripción de la solución'
        ];
    }
}
