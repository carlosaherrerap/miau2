<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsignacionMonitor;
use App\Models\CategoriaAtencion;
use App\Models\Sedereg;
use App\Models\Ticket;
use App\Models\Usuario;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * Indicadores KPI basicos para la pagina de inicio / dashboard
     */
    public function kpis(Request $request)
    {
        $user = $request->user();
        $query = Ticket::query();
        $this->aplicarScopePorRol($query, $user);

        $total = (clone $query)->count();
        $abiertos = (clone $query)->where('estado', 'Abierto')->count();
        $cerrados = (clone $query)->where('estado', 'Cerrado')->count();
        $noProcede = (clone $query)->where('estado', 'No procede')->count();

        return $this->successResponse([
            'total' => $total,
            'abiertos' => $abiertos,
            'cerrados' => $cerrados,
            'no_procede' => $noProcede
        ]);
    }

    /**
     * Metricas consolidadas para el Modulo Gerencial
     */
    public function gerencial(Request $request)
    {
        $user = $request->user();
        $baseQuery = Ticket::query();
        $this->aplicarScopePorRol($baseQuery, $user);

        // 1. Totales generales
        $total = (clone $baseQuery)->count();
        $abiertos = (clone $baseQuery)->where('estado', 'Abierto')->count();
        $cerrados = (clone $baseQuery)->where('estado', 'Cerrado')->count();
        $noProcede = (clone $baseQuery)->where('estado', 'No procede')->count();

        // 2. Tickets por Monitor Informatico
        $ticketsPorMonitor = (clone $baseQuery)
            ->leftJoin('usuario as monitor', 'ticket.id_monitor_responsable', '=', 'monitor.id')
            ->select(
                DB::raw("COALESCE(monitor.codigo_monitor, 'Sin Asignar') as monitor_cod"),
                DB::raw("COALESCE(CONCAT(monitor.nombres, ' ', monitor.ape_pat), 'Sin Asignar') as monitor_nombre"),
                DB::raw('COUNT(ticket.id) as total'),
                DB::raw("COUNT(CASE WHEN ticket.estado = 'Abierto' THEN 1 END) as abiertos"),
                DB::raw("COUNT(CASE WHEN ticket.estado = 'Cerrado' THEN 1 END) as cerrados"),
                DB::raw("COUNT(CASE WHEN ticket.estado = 'No procede' THEN 1 END) as no_procede")
            )
            ->groupBy('monitor.codigo_monitor', 'monitor.nombres', 'monitor.ape_pat')
            ->get();

        // 3. Tickets por Sede Regional
        $ticketsPorSedeReg = (clone $baseQuery)
            ->join('sede_juris', 'ticket.id_sedejuris', '=', 'sede_juris.id')
            ->join('sede_reg', 'sede_juris.id_sedereg', '=', 'sede_reg.id')
            ->select(
                'sede_reg.id as id_sedereg',
                'sede_reg.nombre as sede_regional',
                DB::raw('COUNT(ticket.id) as total'),
                DB::raw("COUNT(CASE WHEN ticket.estado = 'Abierto' THEN 1 END) as abiertos"),
                DB::raw("COUNT(CASE WHEN ticket.estado = 'Cerrado' THEN 1 END) as cerrados"),
                DB::raw("COUNT(CASE WHEN ticket.estado = 'No procede' THEN 1 END) as no_procede")
            )
            ->groupBy('sede_reg.id', 'sede_reg.nombre')
            ->orderBy('total', 'desc')
            ->get();

        // 4. Tickets por Categoria de Atencion
        $ticketsPorCategoria = (clone $baseQuery)
            ->join('categoria_atencion', 'ticket.id_categoria', '=', 'categoria_atencion.id')
            ->select(
                'categoria_atencion.id',
                'categoria_atencion.nombre as categoria',
                DB::raw('COUNT(ticket.id) as total')
            )
            ->groupBy('categoria_atencion.id', 'categoria_atencion.nombre')
            ->orderBy('total', 'desc')
            ->get();

        // 5. Tickets por Tipo de Atencion
        $ticketsPorTipo = (clone $baseQuery)
            ->join('tipo_atencion', 'ticket.id_tipo_atencion', '=', 'tipo_atencion.id')
            ->join('categoria_atencion', 'ticket.id_categoria', '=', 'categoria_atencion.id')
            ->select(
                'categoria_atencion.nombre as categoria',
                'tipo_atencion.nombre as tipo',
                DB::raw('COUNT(ticket.id) as total')
            )
            ->groupBy('categoria_atencion.nombre', 'tipo_atencion.nombre')
            ->orderBy('total', 'desc')
            ->get();

        // 6. Evolucion temporal (ultimos 14 dias o agrupado por fecha)
        $evolucion = (clone $baseQuery)
            ->select(
                DB::raw("TO_CHAR(fecha_emision, 'YYYY-MM-DD') as fecha"),
                DB::raw('COUNT(id) as total'),
                DB::raw("COUNT(CASE WHEN estado = 'Abierto' THEN 1 END) as abiertos"),
                DB::raw("COUNT(CASE WHEN estado = 'Cerrado' THEN 1 END) as cerrados")
            )
            ->groupBy(DB::raw("TO_CHAR(fecha_emision, 'YYYY-MM-DD')"))
            ->orderBy('fecha', 'asc')
            ->get();

        // 7. Promedio de tiempo de resolucion en horas
        $promedioSegundos = (clone $baseQuery)
            ->where('estado', 'Cerrado')
            ->whereNotNull('tiempo_resolucion_segundos')
            ->avg('tiempo_resolucion_segundos');

        $promedioFormateado = '00:00';
        if ($promedioSegundos) {
            $h = floor($promedioSegundos / 3600);
            $m = floor(($promedioSegundos % 3600) / 60);
            $promedioFormateado = sprintf('%02d:%02d', $h, $m);
        }

        return $this->successResponse([
            'resumen' => [
                'total' => $total,
                'abiertos' => $abiertos,
                'cerrados' => $cerrados,
                'no_procede' => $noProcede,
                'tiempo_promedio_resolucion' => $promedioFormateado
            ],
            'por_monitor' => $ticketsPorMonitor,
            'por_sede_regional' => $ticketsPorSedeReg,
            'por_categoria' => $ticketsPorCategoria,
            'por_tipo' => $ticketsPorTipo,
            'evolucion_temporal' => $evolucion
        ]);
    }

    private function aplicarScopePorRol($query, $user)
    {
        if (!$user || !$user->rol) {
            return;
        }

        $rolCod = $user->rol->cod;

        if ($rolCod === 'SAS') {
            $query->where('id_usuario_solicitante', $user->id);
        } elseif ($rolCod === 'MI') {
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
    }
}
