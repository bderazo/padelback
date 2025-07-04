<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;
use App\Services\ReservaService;
use App\Services\MailService;
use App\Traits\ApiResponseTrait;
use Exception;
use App\Mail\ReservaConfirmada;
use Illuminate\Support\Facades\Mail;


class ReservaController extends Controller
{
    use ApiResponseTrait;
    /**
     * Listar reservas con filtros opcionales:
     * - by=cancha (id_cancha)
     * - by=establecimiento (id_establecimiento)
     * - by=fecha (fecha_reserva)
     * - by=horario (fecha_reserva + hora_inicio, hora_fin)
     */
    public function listReservas(Request $request)
    {
        try {
            $query = Reserva::with(['cliente', 'cancha.establecimiento']);

            if ($request->has('by') && $request->has('value')) {
                $by = $request->input('by');
                $value = $request->input('value');

                switch ($by) {
                    case 'cancha':
                        $query->where('id_cancha', $value);
                        break;
                    case 'establecimiento':
                        $query->whereHas('cancha', function ($q) use ($value) {
                            $q->where('id_establecimiento', $value);
                        });
                        break;
                    case 'fecha':
                        $query->whereDate('fecha_reserva', $value);
                        break;
                    case 'horario':
                        $fecha = $request->input('fecha');
                        $hora_inicio = $request->input('inicio');
                        $hora_fin = $request->input('fin');
                        if ($fecha && $hora_inicio && $hora_fin) {
                            $query->where('fecha_reserva', $fecha)
                                ->where(function ($q) use ($hora_inicio, $hora_fin) {
                                    $q->where('hora_inicio', '<', $hora_fin)
                                        ->where('hora_fin', '>', $hora_inicio);
                                });
                        }
                        break;
                }
            }

            $reservas = $query->get();
            return $this->successResponse($reservas, 'Reservas obtenidas', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    /**
     * Obtener una reserva por su ID.
     */
    public function getReservaById($id)
    {
        try {
            $reserva = Reserva::with(['cliente', 'cancha'])->find($id);

            if (!$reserva) {
                return response()->json(['message' => 'Reserva no encontrada'], 404);
            }

            return $this->successResponse($reserva, 'Reserva encontrada', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    /**
     * Crear una nueva reserva.
     */
    public function createReserva(Request $request, MailService $mailService)
    {
        try {
            // Validación completa
            $validated = $request->validate([
                'id_cliente' => 'required|exists:clientes,id_cliente',
                'id_cancha' => 'required|exists:canchas,id_cancha',
                'fecha_reserva' => 'required|date',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i',
                'estado_reserva' => 'required|string|in:pendiente,confirmada,cancelada',
                'monto_total' => 'required|numeric|min:0',
            ]);

            // Verificar conflictos de horario
            $horaInicio = \Carbon\Carbon::createFromFormat('H:i', $validated['hora_inicio']);
            $horaFin = \Carbon\Carbon::createFromFormat('H:i', $validated['hora_fin']);

            if ($horaInicio->gte($horaFin)) {
                return $this->errorResponse('La hora de inicio debe ser menor a la hora de fin.', null, 422);
            }

            $conflicto = \App\Models\Reserva::where('id_cancha', $validated['id_cancha'])
                ->whereDate('fecha_reserva', $validated['fecha_reserva'])
                ->where(function ($q) use ($validated, $horaFin) {
                    $q->where('hora_inicio', '<', $horaFin->format('H:i'))
                        ->where('hora_fin', '>', $validated['hora_inicio']);
                })->exists();

            if ($conflicto) {
                return $this->errorResponse('La cancha ya está reservada en ese horario.', null, 409);
            }

            // Crear reserva con valores del frontend
            $reserva = \App\Models\Reserva::create([
                'id_cliente' => $validated['id_cliente'],
                'id_cancha' => $validated['id_cancha'],
                'fecha_reserva' => $validated['fecha_reserva'],
                'hora_inicio' => $validated['hora_inicio'],
                'hora_fin' => $validated['hora_fin'],
                'monto_total' => $validated['monto_total'],
                'estado_reserva' => $validated['estado_reserva'],
            ]);

            // Enviar correo
            $mailService->sendConfirmationMail(
                $reserva->id_cliente,
                $reserva->fecha_reserva,
                $reserva->hora_inicio,
                $reserva->hora_fin,
                reserva_id: $reserva->id_reserva
            );

            return $this->successResponse($reserva, 'Reserva creada', 200);

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }


    public function calcularMonto(Request $request)
    {
        try {
            $request->validate([
                'id_cancha' => 'required|exists:canchas,id_cancha',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            ]);

            $monto = ReservaService::calcularMontoTotal(
                $request->id_cancha,
                $request->hora_inicio,
                $request->hora_fin
            );

            return $this->successResponse($monto, 'Monto calculado', 200);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }


    /**
     * Actualizar una reserva existente.
     */
    public function updateReserva(Request $request, $id)
    {
        try {
            $reserva = Reserva::find($id);

            if (!$reserva) {
                return response()->json(['message' => 'Reserva no encontrada'], 404);
            }

            $validated = $request->validate([
                'fecha_reserva' => 'sometimes|date',
                'hora_inicio' => 'sometimes|date_format:H:i',
                'hora_fin' => 'sometimes|date_format:H:i|after:hora_inicio',
                'monto_total' => 'sometimes|numeric|min:0',
            ]);

            $reserva->update($validated);

            return $this->successResponse($reserva, 'Reserva actualizada', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    /**
     * Cancelar una reserva (no eliminarla).
     */
    public function cancelReserva($id)
    {
        try {
            $reserva = Reserva::find($id);

            if (!$reserva) {
                return response()->json(['message' => 'Reserva no encontrada'], 404);
            }

            $reserva->estado_reserva = 'cancelada';
            $reserva->save();

            return $this->successResponse($reserva, 'Reserva cancelada correctamente', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    public function confirmarReserva($token)
    {
        try {
            $secret = config('app.key');

            // Buscar una reserva cuyo hash coincida
            $reserva = Reserva::all()->first(function ($reserva) use ($token, $secret) {
                return hash_hmac('sha256', $reserva->id_reserva, $secret) === $token;
            });



            if (!$reserva) {
                return view('reserva_error', ['mensaje' => 'Token inválido o expirado']);
            }

            // Actualizar el estado de la reserva
            $reserva->estado_reserva = 'confirmada';
            $reserva->save();

            //return $this->successResponse($reserva, 'Reserva confirmada correctamente', 200);
            return view('reserva_confirmada', compact('reserva'));

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    public function canchasDisponibles(Request $request)
    {
        $validated = $request->validate([
            'establecimiento_id' => 'required|exists:establecimientos,id_establecimiento',
            'deporte_id' => 'required|exists:deportes,id_deporte',
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);

        $ocupadas = Reserva::whereDate('fecha_reserva', $validated['fecha'])
            ->where(function ($q) use ($validated) {
                $q->where('hora_inicio', '<', $validated['hora_fin'])
                    ->where('hora_fin', '>', $validated['hora_inicio']);
            })->pluck('id_cancha');

        $canchas = \App\Models\Cancha::where('id_establecimiento', $validated['establecimiento_id'])
            ->whereNotIn('id_cancha', $ocupadas)
            ->get();

        return response()->json($canchas);
    }

    // ReservaController.php
    public function reservasPorCliente($id_cliente)
    {
        $reservas = Reserva::with(['cancha.establecimiento', 'cliente'])
            ->where('id_cliente', $id_cliente)
            ->orderByDesc('fecha_reserva')
            ->get();

        return response()->json(['ok' => true, 'data' => $reservas]);
    }

    public function reservasPorEstablecimiento($id_establecimiento)
    {
        $reservas = Reserva::whereHas('cancha', function ($q) use ($id_establecimiento) {
            $q->where('id_establecimiento', $id_establecimiento);
        })->with(['cancha', 'cliente'])
            ->orderByDesc('fecha_reserva')
            ->get();

        return response()->json(['ok' => true, 'data' => $reservas]);
    }

    public function confirmar($id)
    {
        $reserva = Reserva::with(['cliente.user', 'cancha'])->findOrFail($id);
        $reserva->estado_reserva = 'confirmada';
        $reserva->save();

        // Enviar correo
        if ($reserva->cliente && $reserva->cliente->user && $reserva->cliente->user->email) {
            Mail::to($reserva->cliente->user->email)->send(new ReservaConfirmada($reserva));
        }

        return $this->successResponse($reserva, 'Reserva confirmada correctamente.', 200);
    }

    public function cancelar($id)
    {
        $reserva = Reserva::find($id);

        if (!$reserva) {
            return response()->json(['ok' => false, 'message' => 'Reserva no encontrada'], 404);
        }

        $reserva->estado_reserva = 'cancelada';
        $reserva->save();

        return response()->json(['ok' => true, 'message' => 'Reserva cancelada', 'data' => $reserva]);
    }

    public function historial(Request $request)
    {
        $validated = $request->validate([
            'id_cliente' => 'nullable|exists:clientes,id_cliente',
            'id_establecimiento' => 'nullable|exists:establecimientos,id',
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
        ]);

        $query = Reserva::with(['cancha.establecimiento', 'cliente']);

        if (isset($validated['id_cliente'])) {
            $query->where('id_cliente', $validated['id_cliente']);
        }

        if (isset($validated['id_establecimiento'])) {
            $query->whereHas('cancha', function ($q) use ($validated) {
                $q->where('id_establecimiento', $validated['id_establecimiento']);
            });
        }

        if (isset($validated['desde'])) {
            $query->whereDate('fecha_reserva', '>=', $validated['desde']);
        }

        if (isset($validated['hasta'])) {
            $query->whereDate('fecha_reserva', '<=', $validated['hasta']);
        }

        $reservas = $query->orderByDesc('fecha_reserva')->get();

        return response()->json(['ok' => true, 'data' => $reservas]);
    }



}
