<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'usuario';

    protected $fillable = [
        'id_rol',
        'cod_usuario',
        'username',
        'clave',
        'nombres',
        'ape_pat',
        'ape_mat',
        'id_sedereg',
        'id_sedejuris',
        'codigo_monitor',
        'doc',
        'email',
        'estado'
    ];

    protected $hidden = [
        'clave'
    ];

    protected function casts(): array
    {
        return [
            'id_rol' => 'integer',
            'id_sedereg' => 'integer',
            'id_sedejuris' => 'integer',
            'estado' => 'integer',
        ];
    }

    public function getAuthPassword()
    {
        return $this->clave;
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id');
    }

    public function sedeRegional()
    {
        return $this->belongsTo(Sedereg::class, 'id_sedereg', 'id');
    }

    public function sedeJurisdiccional()
    {
        return $this->belongsTo(Sedejuris::class, 'id_sedejuris', 'id');
    }

    public function asignacionesMonitor()
    {
        return $this->hasMany(AsignacionMonitor::class, 'id_usuario_monitor', 'id');
    }

    public function ticketsSolicitados()
    {
        return $this->hasMany(Ticket::class, 'id_usuario_solicitante', 'id');
    }

    public function ticketsMonitoreados()
    {
        return $this->hasMany(Ticket::class, 'id_monitor_responsable', 'id');
    }
}
