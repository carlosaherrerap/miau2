<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsignacionMonitor extends Model
{
    protected $table = 'asignacion_monitor';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario_monitor',
        'id_sedereg',
        'id_sedejuris'
    ];

    public function monitor()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_monitor', 'id');
    }

    public function sedeRegional()
    {
        return $this->belongsTo(Sedereg::class, 'id_sedereg', 'id');
    }

    public function sedeJurisdiccional()
    {
        return $this->belongsTo(Sedejuris::class, 'id_sedejuris', 'id');
    }
}
