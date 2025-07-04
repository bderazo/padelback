<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cancha extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_cancha';
    protected $fillable = ['id_establecimiento', 'nombre', 'tipo', 'precio_por_hora', 'descripcion', 'id_deporte'];
    public $timestamps = true;

    public function establecimiento()
    {
        return $this->belongsTo(Establecimiento::class, 'id_establecimiento', 'id_establecimiento');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'id_cancha', 'id_cancha');
    }

    public function deporte()
    {
        return $this->belongsTo(Deporte::class, 'id_deporte', 'id_deporte');
    }


}
