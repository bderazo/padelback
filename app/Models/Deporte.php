<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deporte extends Model
{
    protected $table = 'deportes';
    protected $primaryKey = 'id_deporte';

    protected $fillable = [
        'titulo',
        'descripcion',
        'precio_base',
        'duracion_base',
    ];

    public $timestamps = true;

    public function canchas()
    {
        return $this->hasMany(Cancha::class, 'id_deporte', 'id_deporte');
    }
}
