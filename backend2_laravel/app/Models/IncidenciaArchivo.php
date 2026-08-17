<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidenciaArchivo extends Model
{
    protected $table = 'incidencia_archivo';
    public $timestamps = false;

    protected $fillable = [
        'id_incidencia',
        'nombre_original',
        'ruta',
        'mime_type',
        'tamano_bytes'
    ];

    public function incidencia()
    {
        return $this->belongsTo(IncidenciaInformativa::class, 'id_incidencia', 'id');
    }
}
