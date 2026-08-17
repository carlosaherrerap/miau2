<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidenciaInformativa extends Model
{
    protected $table = 'incidencia_informativa';

    protected $fillable = [
        'id_usuario_registro',
        'id_sedejuris',
        'tipo_incidencia', // corte_energia, problema_conectividad, aplicativo_movil
        'subtipo_corte', // programado, accidental
        'fecha_inicio',
        'fecha_reanudacion',
        'tiempo_interrupcion_minutos',
        'actividades_afectadas',
        'observaciones',
        'marca_modelo',
        'version_android',
        'aplicativo_afectado',
        'proceso_relacionado',
        'descripcion_problema',
        'descartes_sas',
        'resultado_pruebas',
        'funciona_en_equipo'
    ];

    protected function casts(): array
    {
        return [
            'id_usuario_registro' => 'integer',
            'id_sedejuris' => 'integer',
            'tiempo_interrupcion_minutos' => 'integer',
            'funciona_en_equipo' => 'boolean',
            'fecha_inicio' => 'datetime',
            'fecha_reanudacion' => 'datetime',
        ];
    }

    public function usuarioRegistro()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_registro', 'id');
    }

    public function sedeJurisdiccional()
    {
        return $this->belongsTo(Sedejuris::class, 'id_sedejuris', 'id');
    }

    public function archivos()
    {
        return $this->hasMany(IncidenciaArchivo::class, 'id_incidencia', 'id');
    }
}
