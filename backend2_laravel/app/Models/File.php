<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $table='file';

    protected $fillable = [
        'id_ticket',
        'id_publicacion',
        'tipo',
        'enlace'
    ];

    protected $casts=[
       'id_ticket'=>'integer' 
    ];

    public $timestamps = false;

    public function fk_ticket(){
        return $this->belongsTo(Ticket::class,'id_ticket','id');
    }

    public function fk_publicacion(){
        return $this->belongsTo(Publicacion::class,'id_publicacion','id');
    }
}
