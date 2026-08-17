<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketArchivo extends Model
{
    protected $table = 'ticket_archivo';
    public $timestamps = false;

    protected $fillable = [
        'id_ticket',
        'nombre_original',
        'ruta',
        'mime_type',
        'tamano_bytes'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'id_ticket', 'id');
    }
}
