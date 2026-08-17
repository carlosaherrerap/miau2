<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicacionVista extends Model
{
    protected $table = 'publicacion_vista';
    public $timestamps = false;

    protected $fillable = [
        'id_publicacion',
        'id_usuario_sas',
        'fecha_primera_vista'
    ];

    protected function casts(): array
    {
        return [
            'id_publicacion' => 'integer',
            'id_usuario_sas' => 'integer',
            'fecha_primera_vista' => 'datetime',
        ];
    }

    public function publicacion()
    {
        return $this->belongsTo(Publicacion::class, 'id_publicacion', 'id');
    }

    public function usuarioSas()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_sas', 'id');
    }
}
