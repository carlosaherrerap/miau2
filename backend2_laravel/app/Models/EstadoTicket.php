<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoTicket extends Model
{
    protected $table = 'estado_ticket';
    public $timestamps = false;

    protected $fillable = [
        'id_ticket',
        'id_usuario',
        'estado',
        'fecha_estado',
        'descripcion_solucion',
        'justificacion'
    ];

    protected function casts(): array
    {
        return [
            'id_ticket' => 'integer',
            'id_usuario' => 'integer',
            'fecha_estado' => 'datetime',
        ];
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'id_ticket', 'id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id');
    }
}
