<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;

class ReservaPublicaController extends Controller
{
    public function index()
    {
        $reservas = Reserva::with(['cliente', 'cancha'])
            ->where('estado_reserva', 'pendiente')
            ->get();

        return view('public.reservas.index', compact('reservas'));
    }

    public function confirmar($id)
    {
        $reserva = Reserva::findOrFail($id);
        $reserva->estado_reserva = 'confirmada';
        $reserva->save();

        return redirect('/reservas/pendientes')->with('success', 'Reserva confirmada');
    }
}
