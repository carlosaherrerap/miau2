<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoAtencion extends Model
{
    protected $table = 'tipo_atencion';

    protected $fillable = [
        'id_categoria',
        'nombre',
        'activo'
    ];

    protected function casts(): array
    {
        return [
            'id_categoria' => 'integer',
            'activo' => 'boolean'
        ];
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaAtencion::class, 'id_categoria', 'id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'id_tipo_atencion', 'id');
    }
}
