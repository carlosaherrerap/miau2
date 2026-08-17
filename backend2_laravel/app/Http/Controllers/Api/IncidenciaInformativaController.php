<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncidenciaArchivo;
use App\Models\IncidenciaInformativa;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncidenciaInformativaController extends Controller
{
    use ApiResponse;

    /**
     * Listar incidencias informativas / avisos operativos
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = IncidenciaInformativa::with([
            'usuarioRegistro',
            'sedeJurisdiccional.sedeRegional',
            'archivos'
        ]);

        // Si es SAS, filtrar por sus registros o su sede
        if ($user->rol && $user->rol->cod === 'SAS') {
            $query->where('id_usuario_registro', $user->id);
        }

        if ($request->filled('tipo_incidencia')) {
            $query->where('tipo_incidencia', $request->tipo_incidencia);
        }

        if ($request->filled('id_sedereg')) {
            $query->whereHas('sedeJurisdiccional', function ($q) use ($request) {
                $q->where('id_sedereg', $request->id_sedereg);
            });
        }

        if ($request->filled('id_sedejuris')) {
            $query->where('id_sedejuris', $request->id_sedejuris);
        }

        $query->orderBy('created_at', 'desc');

        return $this->successResponse($query->paginate(15));
    }

    /**
     * Registrar incidencia informativa / aviso operativo
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'tipo_incidencia' => 'required|string|in:corte_energia,problema_conectividad,aplicativo_movil',
            // Cortes / conectividad
            'subtipo_corte' => 'nullable|string|in:programado,accidental',
            'fecha_inicio' => 'nullable|date',
            'fecha_reanudacion' => 'nullable|date',
            'actividades_afectadas' => 'nullable|string',
            'observaciones' => 'nullable|string',
            // Movil
            'marca_modelo' => 'nullable|string',
            'version_android' => 'nullable|string',
            'aplicativo_afectado' => 'nullable|string',
            'proceso_relacionado' => 'nullable|string',
            'descripcion_problema' => 'nullable|string',
            'descartes_sas' => 'nullable|string',
            'resultado_pruebas' => 'nullable|string',
            'funciona_en_equipo' => 'nullable|boolean',
            // Archivos
            'archivos' => 'nullable|array|max:3',
            'archivos.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240'
        ]);

        return DB::transaction(function () use ($user, $validated, $request) {
            $tiempoMinutos = null;
            if (!empty($validated['fecha_inicio']) && !empty($validated['fecha_reanudacion'])) {
                $inicio = Carbon::parse($validated['fecha_inicio']);
                $fin = Carbon::parse($validated['fecha_reanudacion']);
                $tiempoMinutos = $fin->diffInMinutes($inicio);
            }

            $incidencia = IncidenciaInformativa::create([
                'id_usuario_registro' => $user->id,
                'id_sedejuris' => $user->id_sedejuris ?: 1,
                'tipo_incidencia' => $validated['tipo_incidencia'],
                'subtipo_corte' => $validated['subtipo_corte'] ?? null,
                'fecha_inicio' => $validated['fecha_inicio'] ?? null,
                'fecha_reanudacion' => $validated['fecha_reanudacion'] ?? null,
                'tiempo_interrupcion_minutos' => $tiempoMinutos,
                'actividades_afectadas' => $validated['actividades_afectadas'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'marca_modelo' => $validated['marca_modelo'] ?? null,
                'version_android' => $validated['version_android'] ?? null,
                'aplicativo_afectado' => $validated['aplicativo_afectado'] ?? null,
                'proceso_relacionado' => $validated['proceso_relacionado'] ?? null,
                'descripcion_problema' => $validated['descripcion_problema'] ?? null,
                'descartes_sas' => $validated['descartes_sas'] ?? null,
                'resultado_pruebas' => $validated['resultado_pruebas'] ?? null,
                'funciona_en_equipo' => $validated['funciona_en_equipo'] ?? false,
            ]);

            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $mimeType = $file->getMimeType();
                    $sizeBytes = $file->getSize();
                    $path = $file->store("incidencias/{$incidencia->id}", 'public');

                    IncidenciaArchivo::create([
                        'id_incidencia' => $incidencia->id,
                        'nombre_original' => $originalName,
                        'ruta' => $path,
                        'mime_type' => $mimeType,
                        'tamano_bytes' => $sizeBytes
                    ]);
                }
            }

            $incidencia->load(['usuarioRegistro', 'sedeJurisdiccional.sedeRegional', 'archivos']);

            return $this->successResponse($incidencia, 'Aviso operativo / Incidencia registrada exitosamente.', 201);
        });
    }

    /**
     * Actualizar incidencia (por ejemplo registrar fecha de reanudacion)
     */
    public function update(Request $request, string $id)
    {
        $incidencia = IncidenciaInformativa::findOrFail($id);

        $validated = $request->validate([
            'fecha_reanudacion' => 'nullable|date',
            'observaciones' => 'nullable|string'
        ]);

        if (!empty($validated['fecha_reanudacion'])) {
            $incidencia->fecha_reanudacion = $validated['fecha_reanudacion'];
            if ($incidencia->fecha_inicio) {
                $inicio = Carbon::parse($incidencia->fecha_inicio);
                $fin = Carbon::parse($validated['fecha_reanudacion']);
                $incidencia->tiempo_interrupcion_minutos = $fin->diffInMinutes($inicio);
            }
        }

        if (isset($validated['observaciones'])) {
            $incidencia->observaciones = $validated['observaciones'];
        }

        $incidencia->save();
        $incidencia->load(['usuarioRegistro', 'sedeJurisdiccional.sedeRegional', 'archivos']);

        return $this->successResponse($incidencia, 'Incidencia informativa actualizada correctamente.');
    }

    /**
     * Ver detalle de incidencia
     */
    public function show(string $id)
    {
        $incidencia = IncidenciaInformativa::with([
            'usuarioRegistro',
            'sedeJurisdiccional.sedeRegional',
            'archivos'
        ])->findOrFail($id);

        return $this->successResponse($incidencia);
    }
}
