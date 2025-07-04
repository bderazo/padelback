<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstablecimientoHorario extends Model
{
    use HasFactory;

    protected $table = 'establecimiento_horarios';

    protected $fillable = [
        'id_establecimiento',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'estado'
    ];

    public function establecimiento()
    {
        return $this->belongsTo(Establecimiento::class, 'id_establecimiento', 'id_establecimiento');
    }
}