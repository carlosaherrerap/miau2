<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\Sedejuris;
use App\Models\Sedereg;
use App\Models\Usuario;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    use ApiResponse;

    /**
     * Listar usuarios con filtros
     */
    public function index(Request $request)
    {
        $query = Usuario::with(['rol', 'sedeRegional', 'sedeJurisdiccional.sedeRegional']);

        if ($request->filled('id_rol')) {
            $query->where('id_rol', $request->id_rol);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('username', 'ILIKE', "%{$s}%")
                  ->orWhere('cod_usuario', 'ILIKE', "%{$s}%")
                  ->orWhere('nombres', 'ILIKE', "%{$s}%")
                  ->orWhere('ape_pat', 'ILIKE', "%{$s}%")
                  ->orWhere('email', 'ILIKE', "%{$s}%");
            });
        }

        return $this->successResponse($query->orderBy('id', 'asc')->paginate(20));
    }

    /**
     * Carga masiva de usuarios y contraseñas mediante archivo CSV
     * Formato CSV: cod_usuario, username, clave, nombres, ape_pat, ape_mat, rol_cod, sede_reg_cod, sede_juris_cod, doc, email, codigo_monitor
     */
    public function cargaMasiva(Request $request)
    {
        $request->validate([
            'archivo_csv' => 'required|file|mimes:csv,txt|max:5120'
        ]);

        $file = $request->file('archivo_csv');
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return $this->errorResponse('No se pudo abrir el archivo CSV.', 422);
        }

        $header = fgetcsv($handle, 1000, ',');
        $procesados = 0;
        $errores = [];

        DB::transaction(function () use ($handle, &$procesados, &$errores) {
            $roles = Rol::all()->keyBy('cod');
            $sedesReg = Sedereg::all()->keyBy('cod');
            $sedesJuris = Sedejuris::all()->keyBy('cod');

            $linea = 1;
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $linea++;
                if (empty($row) || count($row) < 7) {
                    continue;
                }

                $codUsuario = trim($row[0]);
                $username = trim($row[1]);
                $clave = trim($row[2]);
                $nombres = trim($row[3]);
                $apePat = trim($row[4]);
                $apeMat = isset($row[5]) ? trim($row[5]) : '';
                $rolCod = trim($row[6]);
                $sedeRegCod = isset($row[7]) ? trim($row[7]) : '';
                $sedeJurisCod = isset($row[8]) ? trim($row[8]) : '';
                $doc = isset($row[9]) ? trim($row[9]) : '';
                $email = isset($row[10]) ? trim($row[10]) : '';
                $codigoMonitor = isset($row[11]) ? trim($row[11]) : null;

                $rol = $roles->get($rolCod);
                if (!$rol) {
                    $errores[] = "Línea {$linea}: Rol '{$rolCod}' no existe.";
                    continue;
                }

                $idSedereg = null;
                if (!empty($sedeRegCod) && $sedesReg->has($sedeRegCod)) {
                    $idSedereg = $sedesReg->get($sedeRegCod)->id;
                }

                $idSedejuris = null;
                if (!empty($sedeJurisCod) && $sedesJuris->has($sedeJurisCod)) {
                    $idSedejuris = $sedesJuris->get($sedeJurisCod)->id;
                }

                Usuario::updateOrCreate(
                    ['cod_usuario' => $codUsuario],
                    [
                        'username' => $username,
                        'clave' => Hash::make($clave),
                        'nombres' => $nombres,
                        'ape_pat' => $apePat,
                        'ape_mat' => $apeMat,
                        'id_rol' => $rol->id,
                        'id_sedereg' => $idSedereg,
                        'id_sedejuris' => $idSedejuris,
                        'doc' => $doc,
                        'email' => $email,
                        'codigo_monitor' => $codigoMonitor,
                        'estado' => 1
                    ]
                );

                $procesados++;
            }

            fclose($handle);
        });

        return $this->successResponse([
            'usuarios_procesados' => $procesados,
            'errores' => $errores
        ], "Se procesaron {$procesados} usuarios correctamente.");
    }
}
