<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaAtencion extends Model
{
    protected $table = 'categoria_atencion';

    protected $fillable = [
        'nombre',
        'activo'
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean'
        ];
    }

    public function tipos()
    {
        return $this->hasMany(TipoAtencion::class, 'id_categoria', 'id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'id_categoria', 'id');
    }
}
