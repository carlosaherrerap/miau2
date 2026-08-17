<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Publicacion extends Model
{
    protected $table = 'publicacion';

    protected $fillable = [
        'id_usuario_autor',
        'nombre_aplicativo',
        'version',
        'fecha_publicacion',
        'indicaciones',
        'enlaces_generales',
        'estado' // Activa, Desactivada
    ];

    protected function casts(): array
    {
        return [
            'id_usuario_autor' => 'integer',
            'fecha_publicacion' => 'datetime',
        ];
    }

    public function autor()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_autor', 'id');
    }

    public function archivos()
    {
        return $this->hasMany(PublicacionArchivo::class, 'id_publicacion', 'id');
    }

    public function credenciales()
    {
        return $this->hasMany(PublicacionCredencial::class, 'id_publicacion', 'id');
    }

    public function vistas()
    {
        return $this->hasMany(PublicacionVista::class, 'id_publicacion', 'id');
    }
}
