<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sedereg extends Model
{
    protected $table = 'sede_reg';
    public $timestamps = false;

    protected $fillable = [
        'cod',
        'nombre'
    ];

    public function sedesJuris()
    {
        return $this->hasMany(Sedejuris::class, 'id_sedereg', 'id');
    }

    public function asignaciones()
    {
        return $this->hasMany(AsignacionMonitor::class, 'id_sedereg', 'id');
    }
}
