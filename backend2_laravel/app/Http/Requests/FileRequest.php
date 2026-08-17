<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FileRequest extends FormRequest
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
            'id_ticket' => 'nullable|integer',
            'id_publicacion' => 'nullable|integer',
            'tipo' => 'nullable|string',
            'enlace' => 'nullable|string'
        ];
    }

    public function messages()
    {
        return [
            'id_ticket.integer' => 'El :attribute debe ser un número entero.',
            'id_publicacion.integer' => 'El :attribute debe ser un número entero.',
            'tipo.string' => 'El :attribute debe ser texto.',
            'enlace.string' => 'El :attribute debe ser texto.',
        ];
    }

    public function attributes()
    {
        return [
            'id_ticket' => 'ticket',
            'id_publicacion' => 'publicación',
            'tipo' => 'tipo',
            'enlace' => 'enlace'
        ];
    }



}
