<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicacionCredencial extends Model
{
    protected $table = 'publicacion_credencial';

    protected $fillable = [
        'id_publicacion',
        'id_usuario_sas',
        'cod_sas',
        'datos_personalizados'
    ];

    protected function casts(): array
    {
        return [
            'id_publicacion' => 'integer',
            'id_usuario_sas' => 'integer',
            'datos_personalizados' => 'array',
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
