<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsignacionMonitor;
use App\Models\EstadoTicket;
use App\Models\Ticket;
use App\Models\TicketArchivo;
use App\Models\Usuario;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    use ApiResponse;

    /**
     * Obtener listado de tickets segun rol y filtros
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Ticket::with([
            'solicitante.sedeJurisdiccional.sedeRegional',
            'sedeJurisdiccional.sedeRegional',
            'monitorResponsable',
            'categoria',
            'tipoAtencion',
            'archivos'
        ]);

        // Aplicar restricciones de alcance segun Rol
        $this->aplicarScopePorRol($query, $user);

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('id_sedereg')) {
            $query->whereHas('sedeJurisdiccional', function ($q) use ($request) {
                $q->where('id_sedereg', $request->id_sedereg);
            });
        }

        if ($request->filled('id_sedejuris')) {
            $query->where('id_sedejuris', $request->id_sedejuris);
        }

        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', $request->id_categoria);
        }

        if ($request->filled('id_tipo_atencion')) {
            $query->where('id_tipo_atencion', $request->id_tipo_atencion);
        }

        if ($request->filled('prioridad')) {
            $query->where('prioridad', $request->prioridad);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_emision', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_emision', '<=', $request->fecha_hasta);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('cod_ticket', 'ILIKE', "%{$search}%")
                  ->orWhere('identificador_interno_mi', 'ILIKE', "%{$search}%")
                  ->orWhere('descripcion_problema', 'ILIKE', "%{$search}%")
                  ->orWhereHas('solicitante', function ($sq) use ($search) {
                      $sq->where('nombres', 'ILIKE', "%{$search}%")
                         ->orWhere('ape_pat', 'ILIKE', "%{$search}%")
                         ->orWhere('cod_usuario', 'ILIKE', "%{$search}%");
                  });
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'fecha_emision');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        if ($request->get('all') === 'true' || $request->get('export') === 'true') {
            return $this->successResponse($query->get());
        }

        $perPage = (int) $request->get('per_page', 15);
        $tickets = $query->paginate($perPage);

        return $this->successResponse($tickets);
    }

    /**
     * Registro de nuevo ticket (Exclusivo para Supervisor SAS)
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Validaciones
        $validated = $request->validate([
            'id_categoria' => 'required|integer|exists:categoria_atencion,id',
            'id_tipo_atencion' => 'required|integer|exists:tipo_atencion,id',
            'prioridad' => 'required|string|in:Alta,Media,Baja',
            'descripcion_problema' => 'required|string',
            'archivos' => 'nullable|array|max:3',
            'archivos.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240' // max 10MB
        ], [
            'archivos.max' => 'Se permite adjuntar un máximo de 3 archivos por ticket.',
            'archivos.*.mimes' => 'Los archivos deben corresponder a formatos PDF, Word, Excel o imágenes.'
        ]);

        if (!$user->id_sedejuris) {
            return $this->errorResponse('El usuario no tiene una sede jurisdiccional asignada.', 422);
        }

        return DB::transaction(function () use ($user, $validated, $request) {
            // 1. Determinar el Monitor Informatico responsable de esta sede
            $asignacion = AsignacionMonitor::where('id_sedejuris', $user->id_sedejuris)->first();
            $monitor = null;

            if ($asignacion) {
                $monitor = Usuario::find($asignacion->id_usuario_monitor);
            } else {
                // Fallback por sede regional
                $asignacionReg = AsignacionMonitor::where('id_sedereg', $user->id_sedereg)->first();
                if ($asignacionReg) {
                    $monitor = Usuario::find($asignacionReg->id_usuario_monitor);
                }
            }

            // Si aun no se encuentra, asignar el primer monitor disponible
            if (!$monitor) {
                $monitor = Usuario::whereHas('rol', fn($r) => $r->where('cod', 'MI'))->first();
            }

            $codigoMi = $monitor ? ($monitor->codigo_monitor ?: 'MI1') : 'MI1';

            // 2. Generar correlativo unico para ese monitor
            $ultimoTicketMi = Ticket::where('id_monitor_responsable', $monitor ? $monitor->id : null)
                ->orderBy('correlativo_mi', 'desc')
                ->lockForUpdate()
                ->first();

            $nuevoCorrelativoMi = ($ultimoTicketMi ? $ultimoTicketMi->correlativo_mi : 0) + 1;
            $identificadorInternoMi = sprintf('%s-%03d', $codigoMi, $nuevoCorrelativoMi);

            // 3. Generar numero de ticket general correlativo (000001)
            $ultimoTicket = Ticket::orderBy('id', 'desc')->lockForUpdate()->first();
            $siguienteIdTicket = ($ultimoTicket ? $ultimoTicket->id : 0) + 1;
            $codTicket = sprintf('%06d', $siguienteIdTicket);

            // 4. Crear Ticket
            $ticket = Ticket::create([
                'cod_ticket' => $codTicket,
                'id_usuario_solicitante' => $user->id,
                'id_sedejuris' => $user->id_sedejuris,
                'id_monitor_responsable' => $monitor ? $monitor->id : null,
                'id_categoria' => $validated['id_categoria'],
                'id_tipo_atencion' => $validated['id_tipo_atencion'],
                'prioridad' => $validated['prioridad'],
                'descripcion_problema' => $validated['descripcion_problema'],
                'identificador_interno_mi' => $identificadorInternoMi,
                'correlativo_mi' => $nuevoCorrelativoMi,
                'estado' => 'Abierto',
                'fecha_emision' => Carbon::now()
            ]);

            // 5. Guardar archivos adjuntos si existen
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $mimeType = $file->getMimeType();
                    $sizeBytes = $file->getSize();
                    $path = $file->store("tickets/{$ticket->id}", 'public');

                    TicketArchivo::create([
                        'id_ticket' => $ticket->id,
                        'nombre_original' => $originalName,
                        'ruta' => $path,
                        'mime_type' => $mimeType,
                        'tamano_bytes' => $sizeBytes
                    ]);
                }
            }

            // 6. Registrar en el historial de estados
            EstadoTicket::create([
                'id_ticket' => $ticket->id,
                'id_usuario' => $user->id,
                'estado' => 'Abierto',
                'fecha_estado' => Carbon::now(),
                'descripcion_solucion' => 'Ticket registrado por Supervisor SAS'
            ]);

            $ticket->load([
                'solicitante.sedeJurisdiccional.sedeRegional',
                'sedeJurisdiccional.sedeRegional',
                'monitorResponsable',
                'categoria',
                'tipoAtencion',
                'archivos'
            ]);

            return $this->successResponse($ticket, 'Ticket registrado exitosamente.', 201);
        });
    }

    /**
     * Ver detalle de un ticket
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $ticket = Ticket::with([
            'solicitante.sedeJurisdiccional.sedeRegional',
            'sedeJurisdiccional.sedeRegional',
            'monitorResponsable',
            'categoria',
            'tipoAtencion',
            'archivos',
            'historialEstados.usuario'
        ])->findOrFail($id);

        return $this->successResponse($ticket);
    }

    /**
     * Atender / Resolver / Finalizar ticket (Por Monitor Informatico)
     */
    public function atender(Request $request, string $id)
    {
        $user = $request->user();
        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'estado' => 'required|string|in:Cerrado,No procede',
            'descripcion_resolucion' => 'required_if:estado,Cerrado|nullable|string',
            'justificacion_no_procede' => 'required_if:estado,No procede|nullable|string'
        ]);

        $fechaCierre = Carbon::now();
        $fechaEmision = Carbon::parse($ticket->fecha_emision);
        
        // Calcular tiempo de resolucion transcurrido
        $totalSegundos = $fechaCierre->diffInSeconds($fechaEmision);
        $horas = floor($totalSegundos / 3600);
        $minutos = floor(($totalSegundos % 3600) / 60);
        $segundos = $totalSegundos % 60;
        $tiempoResolucionHms = sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);

        $ticket->estado = $validated['estado'];
        $ticket->fecha_cierre = $fechaCierre;
        $ticket->tiempo_resolucion = $tiempoResolucionHms;
        $ticket->tiempo_resolucion_segundos = $totalSegundos;

        if ($validated['estado'] === 'Cerrado') {
            $ticket->descripcion_resolucion = $validated['descripcion_resolucion'];
            $ticket->justificacion_no_procede = null;
        } else {
            $ticket->justificacion_no_procede = $validated['justificacion_no_procede'];
        }

        $ticket->save();

        // Trazabilidad en estado_ticket
        EstadoTicket::create([
            'id_ticket' => $ticket->id,
            'id_usuario' => $user->id,
            'estado' => $validated['estado'],
            'fecha_estado' => $fechaCierre,
            'descripcion_solucion' => $validated['descripcion_resolucion'] ?? null,
            'justificacion' => $validated['justificacion_no_procede'] ?? null
        ]);

        $ticket->load([
            'solicitante.sedeJurisdiccional.sedeRegional',
            'sedeJurisdiccional.sedeRegional',
            'monitorResponsable',
            'categoria',
            'tipoAtencion',
            'archivos'
        ]);

        return $this->successResponse($ticket, "Ticket actualizado a estado {$ticket->estado}.");
    }

    /**
     * Reclasificacion exclusiva de Categoria y Tipo de Atencion
     * (Disponible para Monitor Informatico, incluso cuando esta Cerrado)
     */
    public function clasificacion(Request $request, string $id)
    {
        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'id_categoria' => 'required|integer|exists:categoria_atencion,id',
            'id_tipo_atencion' => 'required|integer|exists:tipo_atencion,id'
        ]);

        $ticket->id_categoria = $validated['id_categoria'];
        $ticket->id_tipo_atencion = $validated['id_tipo_atencion'];
        $ticket->save();

        $ticket->load(['categoria', 'tipoAtencion']);

        return $this->successResponse($ticket, 'Clasificación de categoría y tipo actualizada correctamente.');
    }

    /**
     * Indicador visual de nuevos tickets pendientes (estado Abierto) para el Monitor
     */
    public function indicadorPendientes(Request $request)
    {
        $user = $request->user();
        $query = Ticket::where('estado', 'Abierto');
        $this->aplicarScopePorRol($query, $user);

        $cantidad = $query->count();

        return $this->successResponse([
            'pendientes' => $cantidad
        ]);
    }

    /**
     * Helper privado para limitar alcance segun Rol
     */
    private function aplicarScopePorRol($query, $user)
    {
        if (!$user || !$user->rol) {
            return;
        }

        $rolCod = $user->rol->cod;

        if ($rolCod === 'SAS') {
            // Solo sus tickets creados
            $query->where('id_usuario_solicitante', $user->id);
        } elseif ($rolCod === 'MI') {
            // Tickets de sus sedes jurisdiccionales asignadas o donde es responsable
            $sedesJurisAsignadas = AsignacionMonitor::where('id_usuario_monitor', $user->id)
                ->whereNotNull('id_sedejuris')
                ->pluck('id_sedejuris')
                ->toArray();

            $sedesRegAsignadas = AsignacionMonitor::where('id_usuario_monitor', $user->id)
                ->whereNotNull('id_sedereg')
                ->pluck('id_sedereg')
                ->toArray();

            $query->where(function ($q) use ($user, $sedesJurisAsignadas, $sedesRegAsignadas) {
                $q->where('id_monitor_responsable', $user->id);

                if (!empty($sedesJurisAsignadas)) {
                    $q->orWhereIn('id_sedejuris', $sedesJurisAsignadas);
                }

                if (!empty($sedesRegAsignadas)) {
                    $q->orWhereHas('sedeJurisdiccional', function ($sq) use ($sedesRegAsignadas) {
                        $sq->whereIn('id_sedereg', $sedesRegAsignadas);
                    });
                }
            });
        }
        // Roles Especialista (EMI), Coordinador (CSMI), Calidad (ECC), Guest tienen acceso global sin filtro
    }
}
