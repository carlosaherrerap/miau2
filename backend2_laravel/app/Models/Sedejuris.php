<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sedejuris extends Model
{
    protected $table = 'sede_juris';
    public $timestamps = false;

    protected $fillable = [
        'id_sedereg',
        'cod',
        'nombre'
    ];

    public function sedeRegional()
    {
        return $this->belongsTo(Sedereg::class, 'id_sedereg', 'id');
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_sedejuris', 'id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'id_sedejuris', 'id');
    }
}
