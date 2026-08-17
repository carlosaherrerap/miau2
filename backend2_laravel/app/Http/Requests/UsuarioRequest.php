<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UsuarioRequest extends FormRequest
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
            'id_rol' => 'required|integer',
            'cod_usuario' => 'required|string',
            'username' => 'required|string|min:3|max:20',
            'clave' => 'nullable|string|min:3|max:50',
            'nombres' => 'required|string|min:3|max:25',
            'ape_pat' => 'required|string|min:3|max:25',
            'ape_mat' => 'string',
            'id_sedereg' => 'required|integer',
            'id_sedejuris' => 'required|integer',
            'doc' => 'required|integer',
            'email' => 'string|min:5|max:40',
            'estado' => 'required|integer'
        ];
    }

    public function messages()
    {
        return [
            'id_rol.required' => 'El rol es requerido',
            'id_rol.integer' => 'El rol debe ser un entero',
            'cod_usuario.required' => 'El codigo de usuario es requerido',
            'cod_usuario.string' => 'El codigo de usuario debe ser una cadena de texto',
            'username.required' => 'El nombre de usuario es requerido',
            'username.string' => 'El nombre de usuario debe ser una cadena de texto',
            'username.min' => 'El nombre de usuario debe tener al menos 3 caracteres',
            'username.max' => 'El nombre de usuario debe tener como maximo 20 caracteres',
            'clave.required' => 'La contraseña es requerida',
            'clave.string' => 'La contraseña debe ser una cadena de texto',
            'clave.min' => 'La contraseña debe tener al menos 3 caracteres',
            'clave.max' => 'La contraseña debe tener como maximo 50 caracteres',
            'nombres.required' => 'El nombre es requerido',
            'nombres.string' => 'El nombre debe ser una cadena de texto',
            'nombres.min' => 'El nombre debe tener al menos 3 caracteres',
            'nombres.max' => 'El nombre debe tener como maximo 25 caracteres',
            'ape_pat.required' => 'El apellido paterno es requerido',
            'ape_pat.string' => 'El apellido paterno debe ser una cadena de texto',
            'ape_pat.min' => 'El apellido paterno debe tener al menos 3 caracteres',
            'ape_pat.max' => 'El apellido paterno debe tener como maximo 25 caracteres',
            'ape_mat.string' => 'El apellido materno debe ser una cadena de texto',
            'id_sedereg.required' => 'La sede de registro es requerida',
            'id_sedereg.integer' => 'La sede de registro debe ser un entero',
            'id_sedejuris.required' => 'La sede de jurisdiccion es requerida',
            'id_sedejuris.integer' => 'La sede de jurisdiccion debe ser un entero',
            'doc.required' => 'El documento es requerido',
            'doc.integer' => 'El documento debe ser un entero',
            'email.required' => 'El correo es requerido',
            'email.string' => 'El correo debe ser una cadena de texto',
            'email.min' => 'El correo debe tener al menos 5 caracteres',
            'email.max' => 'El correo debe tener como maximo 40 caracteres',
            'estado.required' => 'El estado es requerido',
            'estado.integer' => 'El estado debe ser un entero'
        ];
    }

    public function attributes()
    {
        return [
            'id_rol' => 'rol',
            'cod_usuario' => 'codigo de usuario',
            'username' => 'nombre de usuario',
            'clave' => 'contraseña',
            'nombres' => 'nombres',
            'ape_pat' => 'apellido paterno',
            'ape_mat' => 'apellido materno',
            'id_sedereg' => 'sede de registro',
            'id_sedejuris' => 'sede de jurisdiccion',
            'doc' => 'documento',
            'email' => 'correo',
            'estado' => 'estado'
        ];
    }
}
