<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permisosp extends Model
{
    protected $table='permisos_publicacion';

    protected $fillable = [
        'id_publicacion',
        'id_usuario',
        'id_rol',
        'id_sedereg',
        'id_sedejuris',
        'estado'
    ];

    protected $casts = [
        'id_publicacion'=>'integer',
        'id_usuario'=>'integer',
        'id_rol'=>'integer',
        'id_sedereg'=>'integer',
        'id_sedejuris'=>'integer',
        'estado'=>'integer'
    ];

    public $timestamps = false;

    public function fk_publicacion(){
        return $this->belongsTo(Publicacion::class,'id_publicacion','id');
    }

    public function fk_usuario(){
        return $this->belongsTo(Usuario::class,'id_usuario','id');
    }

    public function fk_rol(){
        return $this->belongsTo(Rol::class,'id_rol','id');
    }

    public function fk_sedereg(){
        return $this->belongsTo(Sedereg::class,'id_sedereg','id');
    }

    public function fk_Sedejuris(){
        return $this->belongsTo(Sedejuris::class,'id_sedejuris','id');
    }

}
