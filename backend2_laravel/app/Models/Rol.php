<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'rol';
    public $timestamps = false;

    protected $fillable = [
        'cod',
        'nombre',
        'descripcion'
    ];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_rol', 'id');
    }
}
