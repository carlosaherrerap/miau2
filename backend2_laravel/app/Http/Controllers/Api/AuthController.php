<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Iniciar sesion
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'clave' => 'required|string'
        ]);

        $username = trim($request->username);
        $user = Usuario::with(['rol', 'sedeRegional', 'sedeJurisdiccional.sedeRegional', 'asignacionesMonitor.sedeRegional', 'asignacionesMonitor.sedeJurisdiccional'])
            ->where(function ($query) use ($username) {
                $query->where('username', $username)
                      ->orWhere('cod_usuario', $username);
            })
            ->first();

        if (!Hash::check($request->clave, $user->clave) && $user->clave !== $request->clave) {
            return $this->errorResponse('Credenciales incorrectas.', 401);
        }

        if ($user->estado !== 1) {
            return $this->errorResponse('El usuario se encuentra inactivo.', 403);
        }

        // Crear token de acceso Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'cod_usuario' => $user->cod_usuario,
                'username' => $user->username,
                'nombres' => $user->nombres,
                'ape_pat' => $user->ape_pat,
                'ape_mat' => $user->ape_mat,
                'nombre_completo' => "{$user->nombres} {$user->ape_pat} {$user->ape_mat}",
                'email' => $user->email,
                'codigo_monitor' => $user->codigo_monitor,
                'rol' => $user->rol,
                'sede_regional' => $user->sedeRegional,
                'sede_jurisdiccional' => $user->sedeJurisdiccional,
                'asignaciones_monitor' => $user->asignacionesMonitor
            ]
        ], 'Inicio de sesión exitoso.');
    }

    /**
     * Obtener usuario autenticado
     */
    public function me(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('No autenticado', 401);
        }

        $user->load(['rol', 'sedeRegional', 'sedeJurisdiccional.sedeRegional', 'asignacionesMonitor.sedeRegional', 'asignacionesMonitor.sedeJurisdiccional']);

        return $this->successResponse([
            'id' => $user->id,
            'cod_usuario' => $user->cod_usuario,
            'username' => $user->username,
            'nombres' => $user->nombres,
            'ape_pat' => $user->ape_pat,
            'ape_mat' => $user->ape_mat,
            'nombre_completo' => "{$user->nombres} {$user->ape_pat} {$user->ape_mat}",
            'email' => $user->email,
            'codigo_monitor' => $user->codigo_monitor,
            'rol' => $user->rol,
            'sede_regional' => $user->sedeRegional,
            'sede_jurisdiccional' => $user->sedeJurisdiccional,
            'asignaciones_monitor' => $user->asignacionesMonitor
        ]);
    }

    /**
     * Cerrar sesion
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()->delete();
        }

        return $this->successResponse(null, 'Sesión cerrada exitosamente.');
    }
}
