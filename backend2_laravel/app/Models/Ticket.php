<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'ticket';

    protected $fillable = [
        'cod_ticket',
        'id_usuario_solicitante',
        'id_sedejuris',
        'id_monitor_responsable',
        'id_categoria',
        'id_tipo_atencion',
        'prioridad',
        'descripcion_problema',
        'identificador_interno_mi',
        'correlativo_mi',
        'estado',
        'fecha_emision',
        'fecha_cierre',
        'tiempo_resolucion',
        'tiempo_resolucion_segundos',
        'descripcion_resolucion',
        'justificacion_no_procede'
    ];

    protected function casts(): array
    {
        return [
            'id_usuario_solicitante' => 'integer',
            'id_sedejuris' => 'integer',
            'id_monitor_responsable' => 'integer',
            'id_categoria' => 'integer',
            'id_tipo_atencion' => 'integer',
            'correlativo_mi' => 'integer',
            'tiempo_resolucion_segundos' => 'integer',
            'fecha_emision' => 'datetime',
            'fecha_cierre' => 'datetime',
        ];
    }

    public function solicitante()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_solicitante', 'id');
    }

    public function sedeJurisdiccional()
    {
        return $this->belongsTo(Sedejuris::class, 'id_sedejuris', 'id');
    }

    public function monitorResponsable()
    {
        return $this->belongsTo(Usuario::class, 'id_monitor_responsable', 'id');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaAtencion::class, 'id_categoria', 'id');
    }

    public function tipoAtencion()
    {
        return $this->belongsTo(TipoAtencion::class, 'id_tipo_atencion', 'id');
    }

    public function archivos()
    {
        return $this->hasMany(TicketArchivo::class, 'id_ticket', 'id');
    }

    public function historialEstados()
    {
        return $this->hasMany(EstadoTicket::class, 'id_ticket', 'id');
    }
}
