<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asignacion extends Model
{
    protected $table='asignaciones';

    protected $fillable = [
        'id_usuario',
        'id_rol',
        'id_sedereg',
        'id_sedejuris'
    ];

    protected $casts=[
        'id_usuario'=>'integer',
        'id_rol'=>'integer',
        'id_sedereg'=>'integer',
        'id_sedejuris'=>'integer'
    ];

    public $timestamps = false;

    public function fk_usuario(){
        return $this->belongsTo(Usuario::class,'id_usuario','id');
    }

    public function fk_rol(){
        return $this->belongsTo(Rol::class,'id_rol','id');
    }

    public function fk_sedereg(){
        return $this->belongsTo(Sedereg::class,'id_sedereg','id');
    }

    public function fk_sedejuris(){
        return $this->belongsTo(Sedejuris::class,'id_sedejuris','id');
    }

}
