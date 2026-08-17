<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Publicacion;
use App\Models\PublicacionArchivo;
use App\Models\PublicacionCredencial;
use App\Models\PublicacionVista;
use App\Models\Usuario;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicacionController extends Controller
{
    use ApiResponse;

    /**
     * Listado general de publicaciones (para administracion y consulta global)
     */
    public function index(Request $request)
    {
        $publicaciones = Publicacion::with(['autor', 'archivos'])
            ->withCount([
                'vistas',
                'credenciales'
            ])
            ->orderBy('fecha_publicacion', 'desc')
            ->get();

        return $this->successResponse($publicaciones);
    }

    /**
     * Listado para usuarios SAS (muestra activas e historicas, credenciales solo si activa)
     * Registra automaticamente la primera visualizacion
     */
    public function misPublicaciones(Request $request)
    {
        $user = $request->user();

        $publicaciones = Publicacion::with(['archivos'])
            ->orderBy('fecha_publicacion', 'desc')
            ->get();

        $resultado = [];

        foreach ($publicaciones as $pub) {
            // Verificar si el usuario ya vio la publicacion
            $vista = PublicacionVista::where('id_publicacion', $pub->id)
                ->where('id_usuario_sas', $user->id)
                ->first();

            if (!$vista) {
                // Registrar primera visualizacion
                PublicacionVista::create([
                    'id_publicacion' => $pub->id,
                    'id_usuario_sas' => $user->id,
                    'fecha_primera_vista' => Carbon::now()
                ]);
            }

            // Cargar credenciales personalizadas solo si la publicacion esta Activa
            $credenciales = null;
            if ($pub->estado === 'Activa') {
                $cred = PublicacionCredencial::where('id_publicacion', $pub->id)
                    ->where(function ($q) use ($user) {
                        $q->where('id_usuario_sas', $user->id)
                          ->orWhere('cod_sas', $user->cod_usuario);
                    })
                    ->first();

                if ($cred) {
                    $credenciales = $cred->datos_personalizados;
                }
            }

            $resultado[] = [
                'id' => $pub->id,
                'nombre_aplicativo' => $pub->nombre_aplicativo,
                'version' => $pub->version,
                'fecha_publicacion' => $pub->fecha_publicacion,
                'indicaciones' => $pub->indicaciones,
                'enlaces_generales' => $pub->enlaces_generales,
                'estado' => $pub->estado, // Activa, Desactivada
                'archivos' => $pub->archivos,
                'credenciales_personalizadas' => $credenciales,
                'visualizado' => true
            ];
        }

        return $this->successResponse($resultado);
    }

    /**
     * Crear nueva publicacion
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'nombre_aplicativo' => 'required|string|max:255',
            'version' => 'required|string|max:100',
            'fecha_publicacion' => 'required|date',
            'indicaciones' => 'required|string',
            'enlaces_generales' => 'nullable|string',
            'estado' => 'nullable|string|in:Activa,Desactivada',
            'archivos' => 'nullable|array',
            'archivos.*' => 'file|max:20480'
        ]);

        return DB::transaction(function () use ($user, $validated, $request) {
            $publicacion = Publicacion::create([
                'id_usuario_autor' => $user->id,
                'nombre_aplicativo' => $validated['nombre_aplicativo'],
                'version' => $validated['version'],
                'fecha_publicacion' => $validated['fecha_publicacion'],
                'indicaciones' => $validated['indicaciones'],
                'enlaces_generales' => $validated['enlaces_generales'] ?? null,
                'estado' => $validated['estado'] ?? 'Activa'
            ]);

            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $file) {
                    $path = $file->store("publicaciones/{$publicacion->id}", 'public');
                    PublicacionArchivo::create([
                        'id_publicacion' => $publicacion->id,
                        'nombre_original' => $file->getClientOriginalName(),
                        'ruta' => $path,
                        'mime_type' => $file->getMimeType(),
                        'tamano_bytes' => $file->getSize()
                    ]);
                }
            }

            $publicacion->load(['autor', 'archivos']);
            return $this->successResponse($publicacion, 'Publicación creada exitosamente.', 201);
        });
    }

    /**
     * Actualizar publicacion o cambiar vigencia
     */
    public function update(Request $request, string $id)
    {
        $publicacion = Publicacion::findOrFail($id);

        $validated = $request->validate([
            'nombre_aplicativo' => 'sometimes|required|string|max:255',
            'version' => 'sometimes|required|string|max:100',
            'indicaciones' => 'sometimes|required|string',
            'enlaces_generales' => 'nullable|string',
            'estado' => 'sometimes|required|string|in:Activa,Desactivada'
        ]);

        $publicacion->update($validated);
        $publicacion->load(['autor', 'archivos']);

        return $this->successResponse($publicacion, 'Publicación actualizada correctamente.');
    }

    /**
     * Cargar archivo CSV con credenciales personalizadas para usuarios SAS
     * Formato CSV: SAS ID, Dato1, Valor1, Dato2, Valor2, ...
     */
    public function cargarCredencialesCsv(Request $request, string $id)
    {
        $publicacion = Publicacion::findOrFail($id);

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

        DB::transaction(function () use ($handle, $publicacion, &$procesados) {
            // Opcional: limpiar credenciales anteriores de esta publicacion
            PublicacionCredencial::where('id_publicacion', $publicacion->id)->delete();

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (empty($row) || empty($row[0])) {
                    continue;
                }

                $codSas = trim($row[0]);
                $usuarioSas = Usuario::where('cod_usuario', $codSas)
                    ->orWhere('username', strtolower($codSas))
                    ->first();

                $datosPares = [];
                $totalCols = count($row);

                // Agrupar pares (DatoN, ValorN)
                for ($i = 1; $i < $totalCols; $i += 2) {
                    $label = isset($row[$i]) ? trim($row[$i]) : '';
                    $value = isset($row[$i + 1]) ? trim($row[$i + 1]) : '';

                    if (!empty($label) || !empty($value)) {
                        $datosPares[] = [
                            'label' => $label,
                            'value' => $value
                        ];
                    }
                }

                PublicacionCredencial::create([
                    'id_publicacion' => $publicacion->id,
                    'id_usuario_sas' => $usuarioSas ? $usuarioSas->id : 0,
                    'cod_sas' => $codSas,
                    'datos_personalizados' => $datosPares
                ]);

                $procesados++;
            }

            fclose($handle);
        });

        return $this->successResponse([
            'registros_cargados' => $procesados
        ], "Se cargaron exitosamente las credenciales de {$procesados} usuarios SAS.");
    }

    /**
     * Seguimiento de visualizaciones de la publicacion por usuarios SAS
     * Muestra estado Pendiente / Visualizado y fecha de primera visualizacion
     */
    public function seguimiento(Request $request, string $id)
    {
        $publicacion = Publicacion::findOrFail($id);

        // Obtener todos los usuarios SAS activos
        $query = Usuario::whereHas('rol', fn($r) => $r->where('cod', 'SAS'))
            ->with(['sedeRegional', 'sedeJurisdiccional.sedeRegional'])
            ->where('estado', 1);

        if ($request->filled('id_sedereg')) {
            $query->where('id_sedereg', $request->id_sedereg);
        }

        if ($request->filled('id_sedejuris')) {
            $query->where('id_sedejuris', $request->id_sedejuris);
        }

        $usuariosSas = $query->orderBy('cod_usuario', 'asc')->get();

        // Obtener vistas registradas para esta publicacion
        $vistas = PublicacionVista::where('id_publicacion', $publicacion->id)
            ->get()
            ->keyBy('id_usuario_sas');

        $filtroEstado = $request->get('estado_visualizacion'); // 'Pendiente' o 'Visualizado'
        $resultado = [];

        foreach ($usuariosSas as $sas) {
            $vista = $vistas->get($sas->id);
            $visualizado = $vista !== null;
            $estadoVisualizacion = $visualizado ? 'Visualizado' : 'Pendiente';
            $fechaVisualizacion = $visualizado ? Carbon::parse($vista->fecha_primera_vista)->format('d/m/Y H:i') : '-';

            if ($filtroEstado && $estadoVisualizacion !== $filtroEstado) {
                continue;
            }

            $resultado[] = [
                'id_usuario' => $sas->id,
                'cod_usuario' => $sas->cod_usuario,
                'nombres' => "{$sas->nombres} {$sas->ape_pat} {$sas->ape_mat}",
                'sede_regional' => $sas->sedeRegional ? $sas->sedeRegional->nombre : ($sas->sedeJurisdiccional && $sas->sedeJurisdiccional->sedeRegional ? $sas->sedeJurisdiccional->sedeRegional->nombre : '-'),
                'sede_jurisdiccional' => $sas->sedeJurisdiccional ? $sas->sedeJurisdiccional->nombre : '-',
                'estado_visualizacion' => $estadoVisualizacion,
                'fecha_visualizacion' => $fechaVisualizacion,
                'is_visualizado' => $visualizado
            ];
        }

        // Ordenar con 'Pendiente' primero por defecto como solicita el PDF
        usort($resultado, function ($a, $b) {
            if ($a['estado_visualizacion'] === $b['estado_visualizacion']) {
                return strcmp($a['cod_usuario'], $b['cod_usuario']);
            }
            return $a['estado_visualizacion'] === 'Pendiente' ? -1 : 1;
        });

        return $this->successResponse([
            'publicacion' => $publicacion,
            'total_sas' => count($usuariosSas),
            'visualizados' => $vistas->count(),
            'pendientes' => count($usuariosSas) - $vistas->count(),
            'detalle' => $resultado
        ]);
    }
}
