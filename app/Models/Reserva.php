<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_reserva';
    protected $fillable = ['id_cliente', 'id_cancha', 'fecha_reserva', 'hora_inicio', 'hora_fin', 'estado_reserva', 'monto_total'];
    public $timestamps = true;

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function cancha()
    {
        return $this->belongsTo(Cancha::class, 'id_cancha', 'id_cancha');
    }

}
