<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicacionArchivo extends Model
{
    protected $table = 'publicacion_archivo';
    public $timestamps = false;

    protected $fillable = [
        'id_publicacion',
        'nombre_original',
        'ruta',
        'mime_type',
        'tamano_bytes'
    ];

    public function publicacion()
    {
        return $this->belongsTo(Publicacion::class, 'id_publicacion', 'id');
    }
}
