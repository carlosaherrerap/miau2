<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vistap extends Model
{
    protected $table = 'vista_publicacion';

    protected $fillable = [
        'id_usuario',
        'id_publicacion',
        'fecha_vista'
    ];

    protected $casts = [
        'id_usuario' => 'integer',
        'id_publicacion' => 'integer',
        'fecha_vista' => 'datetime'
    ];

    public $timestamps = false;

    public function fk_usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id');
    }

    public function fk_publicacion()
    {
        return $this->belongsTo(Publicacion::class, 'id_publicacion', 'id');
    }
}
