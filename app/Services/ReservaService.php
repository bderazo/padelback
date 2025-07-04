<?php

namespace App\Services;

use App\Models\Cancha;
use Carbon\Carbon;

class ReservaService
{
    public static function calcularMontoTotal(int $idCancha, string $horaInicio, string $horaFin): float
    {
        $cancha = Cancha::findOrFail($idCancha);

        $inicio = Carbon::createFromFormat('H:i', $horaInicio);
        $fin = Carbon::createFromFormat('H:i', $horaFin);

        $duracionHoras = $inicio->diffInMinutes($fin) / 60;

        return $duracionHoras * $cancha->precio_por_hora;
    }
}
