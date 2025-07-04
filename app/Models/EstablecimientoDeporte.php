<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstablecimientoDeporte extends Model
{
    protected $table = 'establecimiento_deporte';

    protected $fillable = [
        'establecimiento_id',
        'deporte_id',
        'precio',
        'duracion',
    ];

    public function deporte()
    {
        return $this->belongsTo(Deporte::class, 'deporte_id', 'id_deporte');
    }

    public function establecimiento()
    {
        return $this->belongsTo(Establecimiento::class);
    }
}
