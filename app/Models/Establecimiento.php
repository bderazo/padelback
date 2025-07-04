<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Establecimiento extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_establecimiento';
    protected $fillable = ['nombre', 'ciudad', 'direccion', 'telefono_contacto', 'correo_contacto', 'id_administrador',];
    public $timestamps = true;

    public function canchas()
    {
        return $this->hasMany(Cancha::class, 'id_establecimiento', 'id_establecimiento');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'id_establecimiento', 'id_establecimiento')->where('role', 'admin');
    }

    public function deportes()
    {
        return $this->hasMany(\App\Models\EstablecimientoDeporte::class, 'establecimiento_id', 'id_establecimiento');
    }

    public function administrador()
    {
        return $this->belongsTo(User::class, 'id_administrador');
    }

    public function horarios()
    {
        return $this->hasMany(EstablecimientoHorario::class, 'id_establecimiento');
    }

}
