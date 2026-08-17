<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estadot extends Model
{
    protected $table='estado_ticket';

    protected $fillable = [
        'id_ticket',
        'estado',
        'fecha_estado_actual',
        'descripcion_solucion'
    ];

    protected $casts = [
        'id_ticket'=>'integer',
        'fecha_estado_actual'=>'datetime',
    ];

    public $timestamps = false;

    public function fk_ticket(){
        return $this->belongsTo(Ticket::class,'id_ticket','id');
    }

}
