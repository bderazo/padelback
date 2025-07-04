<?php

namespace App\Http\Controllers;

use App\Models\Cancha;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CanchaController extends Controller
{

    use ApiResponseTrait;

    /**
     * Listar todas las canchas.
     */
    public function listCanchas()
    {
        try {
            $canchas = Cancha::with('establecimiento')->get();
            return $this->successResponse($canchas, 'Canchas obtenidas exitosamente', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    public function uploadCanchaImagen(Request $request, $id)
    {
        $request->validate([
            'imagen' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $cancha = Cancha::findOrFail($id);

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('imagen', 'public');
            $url = Storage::url($path); // genera /storage/logos/imagen.jpg

            $cancha->imagen_url = $url;
            $cancha->save();
        }

        return response()->json([
            'ok' => true,
            'message' => 'Imagen actualizada correctamente',
            'data' => $cancha
        ]);
    }

    /**
     * Obtener una cancha por su ID.
     */
    public function getCanchaById($id)
    {
        try {
            $cancha = Cancha::with('establecimiento')->find($id);
            if (!$cancha) {
                return response()->json(['message' => 'Cancha no encontrada'], 404);
            }
            return $this->successResponse($cancha, 'Cancha encontrada', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    /**
     * Obtener canchas por ID de establecimiento.
     */
    public function getCanchasByEstablecimientoId($idEstablecimiento)
    {
        try {
            $canchas = Cancha::where('id_establecimiento', $idEstablecimiento)->get();
            return $this->successResponse($canchas, 'Canchas obtenidas exitosamente', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    /**
     * Listar canchas disponibles por fecha, hora y tipo.
     */
    public function listAvailableCanchas(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
                'fecha' => 'required|string|size:8', // Formato YYYYMMDD
                'inicio' => 'required|string|size:4', // Formato HHMM
                'fin' => 'required|string|size:4',    // Formato HHMM
            ]);

            // Transformar los formatos
            $idEstablecimiento = $request->input('id');

            // Convertir fecha de YYYYMMDD a YYYY-MM-DD
            $fechaStr = $request->input('fecha');
            $fecha = substr($fechaStr, 0, 4) . '-' . substr($fechaStr, 4, 2) . '-' . substr($fechaStr, 6, 2);

            // Convertir hora de HHMM a HH:MM
            $horaInicioStr = $request->input('inicio');
            $horaInicio = substr($horaInicioStr, 0, 2) . ':' . substr($horaInicioStr, 2, 2);

            $horaFinStr = $request->input('fin');
            $horaFin = substr($horaFinStr, 0, 2) . ':' . substr($horaFinStr, 2, 2);

            // Validar que la hora de fin sea posterior a la de inicio
            if (strtotime($horaFin) <= strtotime($horaInicio)) {
                return response()->json(['error' => 'La hora de fin debe ser posterior a la hora de inicio'], 422);
            }

            $byType = $request->input('tipo');

            // Obtener IDs de canchas reservadas en ese rango
            $canchasReservadas = Reserva::where('fecha_reserva', $fecha)
                ->where('estado_reserva', '!=', 'cancelada')
                ->whereHas('cancha', function ($q) use ($idEstablecimiento) {
                    $q->where('id_establecimiento', $idEstablecimiento);
                })
                ->where(function ($query) use ($horaInicio, $horaFin) {
                    $query->where(function ($q) use ($horaInicio, $horaFin) {
                        $q->where('hora_inicio', '<', $horaFin)
                            ->where('hora_fin', '>', $horaInicio);
                    });
                })
                ->pluck('id_cancha');

            // Buscar canchas disponibles que no estén en las reservadas
            $query = Cancha::where('id_establecimiento', $idEstablecimiento)
                ->whereNotIn('id_cancha', $canchasReservadas);

            if ($byType) {
                $query->where('tipo', $byType);
            }

            $canchasDisponibles = $query->get();


            return $this->successResponse($canchasDisponibles, 'Canchas disponibles obtenidas exitosamente', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    /*
    /* Crear una sola cancha
    */
    public function createCancha(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_establecimiento' => 'required|exists:establecimientos,id_establecimiento',
                'nombre' => 'required|string|max:255',
                'tipo' => 'required|string|max:100',
                'precio_por_hora' => 'required|numeric|min:0',
                'descripcion' => 'nullable|string',
                'id_deporte' => 'nullable|exists:deportes,id_deporte',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $cancha = Cancha::create($validator->validated());

            // Subir logo si existe
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('canchas', 'public');
                $cancha->imagen_url = $path;
                $cancha->save();
            }

            return $this->successResponse($cancha, 'Cancha creada correctamente', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }


    /* Crear múltiples canchas a la vez
     */
    public function bulkCreateCanchas(Request $request)
    {
        try {
            $data = $request->all();

            if (!is_array($data)) {
                return response()->json(['error' => 'Se espera un arreglo de canchas.'], 422);
            }

            $rules = [
                'id_establecimiento' => 'required|exists:establecimientos,id_establecimiento',
                'nombre' => 'required|string|max:255',
                'tipo' => 'required|string|max:100',
                'precio_por_hora' => 'required|numeric|min:0',
                'descripcion' => 'nullable|string',
            ];

            $created = [];
            $errors = [];

            foreach ($data as $index => $canchaData) {
                $validator = Validator::make($canchaData, $rules);

                if ($validator->fails()) {
                    $errors[$index] = $validator->errors();
                    continue;
                }

                $created[] = Cancha::create($validator->validated());
            }


            $canchaData = [
                'creadas' => $created,
                'errores' => $errors
            ];

            return $this->successResponse($canchaData, 'Canchas creadas exitosamente', 207);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    public function changeStatus($id, Request $request)
    {
        try {
            $request->validate([
                'estado' => 'required|boolean',
            ]);

            $cancha = Cancha::find($id);

            if (!$cancha) {
                return $this->errorResponse(null, 'Cancha no encontrada', 404);
            }

            $cancha->estado = $request->estado;
            $cancha->save();

            return $this->successResponse($cancha, 'Estado de la cancha actualizado correctamente', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    public function updateCancha(Request $request, $id)
    {
        $response = null;
        try {
            $cancha = Cancha::find($id);

            if (!$cancha) {
                $response = $this->errorResponse(null, 'Cancha no encontrada', 404);
            } else {
                $validator = Validator::make($request->all(), [
                    'id_establecimiento' => 'sometimes|exists:establecimientos,id_establecimiento',
                    'nombre' => 'sometimes|string|max:255',
                    'tipo' => 'sometimes|string|max:100',
                    'precio_por_hora' => 'sometimes|numeric|min:0',
                    'descripcion' => 'nullable|string',
                    'id_deporte' => 'nullable|exists:deportes,id_deporte',
                    'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                ]);

                if ($validator->fails()) {
                    $response = response()->json(['errors' => $validator->errors()], 422);
                } else {
                    $cancha->update($validator->validated());

                    // Subir logo si existe
                    if ($request->hasFile('logo')) {
                        $path = $request->file('logo')->store('canchas', 'public');
                        $cancha->imagen_url = $path;
                        $cancha->save();
                    }

                    $response = $this->successResponse($cancha, 'Cancha actualizada correctamente', 200);
                }
            }
        } catch (Exception $e) {
            $response = $this->errorResponse($e->getMessage(), null, 500);
        }
        return $response;
    }

    /**
     * Obtener fechas disponibles para una cancha específica.
     */
    public function getFechasDisponibles($id)
    {
        try {
            $cancha = Cancha::with('establecimiento.horarios')->find($id);
            if (!$cancha) {
                return $this->errorResponse(null, 'Cancha no encontrada', 404);
            }

            $establecimiento = $cancha->establecimiento;

            if (!$establecimiento || $establecimiento->horarios->isEmpty()) {
                return $this->errorResponse(null, 'El establecimiento no tiene horarios configurados', 422);
            }

            $duracionReserva = 1; // en horas
            $fechasDisponibles = [];

            for ($i = 0; $i < 15; $i++) {
                $fecha = Carbon::today()->addDays($i);
                $diaSemana = $fecha->dayOfWeek; // 0 = domingo, 6 = sábado

                // Buscar horario del establecimiento para este día
                $horarioDia = $establecimiento->horarios->firstWhere('dia_semana', $diaSemana);

                if (
                    !$horarioDia ||
                    !$horarioDia->estado ||
                    empty($horarioDia->hora_inicio) ||
                    empty($horarioDia->hora_fin)
                ) {
                    \Log::info("Día $diaSemana ignorado por falta de horario válido o inactivo", [
                        'estado' => $horarioDia->estado ?? null,
                        'hora_inicio' => $horarioDia->hora_inicio ?? null,
                        'hora_fin' => $horarioDia->hora_fin ?? null,
                    ]);
                    continue; // Día no laborable o mal configurado
                }

                $horaInicioDia = Carbon::createFromTimeString($horarioDia->hora_inicio);
                $horaFinDia = Carbon::createFromTimeString($horarioDia->hora_fin);

                // Obtener reservas para esa fecha
                $reservas = Reserva::where('id_cancha', $id)
                    ->where('fecha_reserva', $fecha->toDateString())
                    ->where('estado_reserva', '!=', 'cancelada')
                    ->get();

                // Construir bloques ocupados
                $horariosOcupados = [];
                foreach ($reservas as $reserva) {
                    $horariosOcupados[] = [
                        'inicio' => Carbon::parse($reserva->hora_inicio),
                        'fin' => Carbon::parse($reserva->hora_fin)
                    ];
                }

                // Verificar si hay al menos un bloque disponible
                $hayDisponibilidad = false;
                $hora = $horaInicioDia->copy();
                while ($hora->lt($horaFinDia)) {
                    $bloqueFin = $hora->copy()->addHours($duracionReserva);
                    $estaLibre = true;

                    foreach ($horariosOcupados as $h) {
                        if (
                            $hora->lt($h['fin']) &&
                            $bloqueFin->gt($h['inicio'])
                        ) {
                            $estaLibre = false;
                            break;
                        }
                    }

                    if ($estaLibre) {
                        $hayDisponibilidad = true;
                        break;
                    }

                    $hora->addHours($duracionReserva);
                }
                \Log::info("Disponibilidad para {$fecha->toDateString()} ({$diaSemana}):", [
                    'hayDisponibilidad' => $hayDisponibilidad,
                    'hora_inicio' => $horarioDia->hora_inicio,
                    'hora_fin' => $horarioDia->hora_fin,
                    'reservas' => $reservas->count(),
                ]);

                if ($hayDisponibilidad) {
                    $fechasDisponibles[] = $fecha->toDateString();
                }
            }

            return $this->successResponse($fechasDisponibles, 'Fechas disponibles', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    /**
     * Obtener horarios disponibles para una cancha en una fecha específica.
     */
    public function getHorariosDisponiblesPorFecha($id, Request $request)
    {
        $fecha = $request->query('fecha');

        try {
            $cancha = Cancha::with(['establecimiento.horarios', 'deporte'])->find($id);
            if (!$cancha) {
                return $this->errorResponse(null, 'Cancha no encontrada', 404);
            }

            $establecimiento = $cancha->establecimiento;
            if (!$establecimiento || $establecimiento->horarios->isEmpty()) {
                return $this->errorResponse(null, 'El establecimiento no tiene horarios configurados', 422);
            }

            $diaSemana = Carbon::parse($fecha)->dayOfWeek;
            $horarioDia = $establecimiento->horarios->firstWhere('dia_semana', $diaSemana);

            if (
                !$horarioDia ||
                !$horarioDia->estado ||
                empty($horarioDia->hora_inicio) ||
                empty($horarioDia->hora_fin) ||
                Carbon::parse($horarioDia->hora_inicio)->gte(Carbon::parse($horarioDia->hora_fin))
            ) {
                return $this->errorResponse(null, 'El día no está habilitado para reservas', 422);
            }

            $horaInicio = Carbon::parse($horarioDia->hora_inicio);
            $horaFin = Carbon::parse($horarioDia->hora_fin);
            $bloques = [];

            $duracionMinutos = $cancha->deporte->duracion_base ?? 60;
            $tiempoTransicion = 10;
            
            $reservas = Reserva::where('id_cancha', $id)
                ->where('fecha_reserva', $fecha)
                ->where('estado_reserva', '!=', 'cancelada')
                ->get();

            $ocupados = [];
            foreach ($reservas as $r) {
                $ocupados[] = [
                    'inicio' => Carbon::parse($r->hora_inicio),
                    'fin' => Carbon::parse($r->hora_fin),
                ];
            }

            while ($horaInicio->copy()->addMinutes($duracionMinutos)->lte($horaFin)) {
                $bloqueInicio = $horaInicio->copy();
                $bloqueFin = $horaInicio->copy()->addMinutes($duracionMinutos);
                $estaLibre = true;

                foreach ($ocupados as $r) {
                    if (
                        $bloqueInicio->lt($r['fin']) &&
                        $bloqueFin->gt($r['inicio'])
                    ) {
                        $estaLibre = false;
                        break;
                    }
                }

                if ($estaLibre) {
                    $bloques[] = [
                        'inicio' => $bloqueInicio->format('H:i'),
                        'fin' => $bloqueFin->format('H:i'),
                    ];
                }

                $horaInicio->addMinutes($duracionMinutos + $tiempoTransicion); // Agregar tiempo de transición
            }

            return $this->successResponse($bloques, 'Horarios disponibles', 200);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

}
