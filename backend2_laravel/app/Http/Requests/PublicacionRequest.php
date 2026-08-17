<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PublicacionRequest extends FormRequest
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
            'version' => 'required|decimal:0,2',
            'fecha_publicacion' => 'required|date_format:Y-m-d H:i:s',
            'fecha_vigencia' => 'required|date_format:Y-m-d H:i:s',
            'indicaciones' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El :attribute es requerido.',
            'nombre.string' => 'El :attribute debe ser una cadena de texto.',
            'version.required' => 'El :attribute es requerido.',
            'version.decimal' => 'El :attribute debe ser un número decimal.',
            'fecha_publicacion.required' => 'El :attribute es requerido.',
            'fecha_publicacion.date_format' => 'El :attribute debe tener el formato Y-m-d H:i:s.',
            'fecha_vigencia.required' => 'El :attribute es requerido.',
            'fecha_vigencia.date_format' => 'El :attribute debe tener el formato Y-m-d H:i:s.',
            'indicaciones.required' => 'El :attribute es requerido.',
            'indicaciones.string' => 'El :attribute debe ser una cadena de texto.'
        ];
    }

    public function attributes()
    {
        return [
            'nombre' => 'nombre',
            'version' => 'versión',
            'fecha_publicacion' => 'fecha de publicación',
            'fecha_vigencia' => 'fecha de vigencia',
            'indicaciones' => 'indicaciones'
        ];
    }







}
