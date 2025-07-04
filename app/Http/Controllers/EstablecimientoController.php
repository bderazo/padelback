<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Support\Facades\Storage;

class EstablecimientoController extends Controller
{
    use ApiResponseTrait;
    /**
     * Listar todos los establecimientos.
     */
    public function listEstablecimientos()
    {
        try {
            $establecimientos = Establecimiento::with([
                'canchas.reservas',
                'usuarios',    // Solo admins, según el modelo
                'deportes.deporte', // Suponiendo que EstablecimientoDeporte tiene una relación `deporte()`
                'horarios' // Relación con horarios
            ])->get();

            return $this->successResponse($establecimientos, 'Establecimientos obtenidos', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    public function updateLogo(Request $request, $id)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $establecimiento = Establecimiento::findOrFail($id);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $url = Storage::url($path); // genera /storage/logos/imagen.jpg

            $establecimiento->logo = $url;
            $establecimiento->save();
        }

        return response()->json([
            'ok' => true,
            'message' => 'Logo actualizado correctamente',
            'data' => $establecimiento
        ]);
    }

    /**
     * Crear un nuevo establecimiento.
     */
    public function createEstablecimiento(Request $request)
    {
        try {
            $diaSemanaMap = [
                'Lunes' => 0,
                'Martes' => 1,
                'Miércoles' => 2,
                'Jueves' => 3,
                'Viernes' => 4,
                'Sábado' => 5,
                'Domingo' => 6,
            ];

            // Validar datos del establecimiento
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'ciudad' => 'required|string|max:100',
                'direccion' => 'required|string|max:255',
                'telefono_contacto' => 'nullable|string|max:20',
                'correo_contacto' => 'nullable|email|max:255',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'id_administrador' => 'nullable|exists:users,id',
                'horarios' => 'required|string', // llegará como JSON string
            ]);

            // Validar administrador
            if ($request->filled('id_administrador')) {
                $admin = \App\Models\User::find($request->id_administrador);
                if (!$admin || $admin->role !== 'admin') {
                    return response()->json([
                        'message' => 'El usuario seleccionado no es un administrador válido',
                    ], 422);
                }
            }

            // Crear establecimiento
            $establecimiento = Establecimiento::create($validated);

            // Subir logo si existe
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('logos', 'public');
                $establecimiento->logo = $path;
                $establecimiento->save();
            }

            // Guardar horarios
            $horarios = json_decode($request->horarios, true);
            if (!is_array($horarios)) {
                return response()->json([
                    'message' => 'Formato de horarios inválido',
                ], 422);
            }

            if ($request->filled('horarios')) {
                $horarios = json_decode($request->horarios, true); // si es string JSON

                foreach ($horarios as $h) {
                    if (!isset($diaSemanaMap[$h['dia']])) {
                        continue; // o lanzar error si prefieres
                    }

                    $establecimiento->horarios()->create([
                        'id_establecimiento' => $establecimiento->id_establecimiento,
                        'dia_semana' => $diaSemanaMap[$h['dia']],
                        'activo' => $h['activo'],
                        'hora_inicio' => $h['hora_inicio'],
                        'hora_fin' => $h['hora_fin'],
                        'estado' => $h['activo'] ?? 0, // por defecto activo
                    ]);
                }
            }


            return $this->successResponse($establecimiento->load('horarios'), 'Establecimiento creado', 201);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    /**
     * Mostrar un establecimiento por su ID.
     */
    public function getEstablecimientoById($id)
    {
        try {
            $establecimiento = Establecimiento::find($id);

            if (!$establecimiento) {
                return response()->json(['message' => 'Establecimiento no encontrado'], 404);
            }

            return $this->successResponse($establecimiento, 'Establecimiento encontrado', 200);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    /**
     * Actualizar un establecimiento existente.
     */
    public function updateEstablecimiento(Request $request, $id)
    {
        \Log::info('Iniciando actualización del establecimiento', ['id' => $id]);
        \Log::info('Campos recibidos: ', $request->all());

        $response = null;

        try {
            $establecimiento = Establecimiento::find($id);

            if (!$establecimiento) {
                $response = response()->json(['message' => 'Establecimiento no encontrado'], 404);
            } else {
                \Log::info('Establecimiento encontrado', ['data' => $establecimiento]);

                // Validar campos
                $request->validate([
                    'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                    'id_administrador' => 'nullable|exists:users,id',
                ]);

                // Verificar que el usuario seleccionado sea admin (si se envía)
                if ($request->filled('id_administrador')) {
                    $admin = \App\Models\User::find($request->id_administrador);
                    if (!$admin || $admin->role !== 'admin') {
                        $response = response()->json(['message' => 'El usuario seleccionado no es un administrador válido'], 422);
                    }
                }

                if (!$response) {
                    // Campos esperados
                    $validated = $request->only([
                        'nombre',
                        'ciudad',
                        'direccion',
                        'telefono_contacto',
                        'correo_contacto',
                        'id_administrador',
                    ]);

                    \Log::info('Datos validados', ['validated' => $validated]);

                    // Actualizar datos
                    $establecimiento->update($validated);
                    \Log::info('Datos del establecimiento actualizados');

                    // Procesar nuevo logo si fue enviado
                    if ($request->hasFile('logo')) {
                        \Log::info('Se envió nuevo logo');

                        if ($establecimiento->logo && Storage::disk('public')->exists($establecimiento->logo)) {
                            Storage::disk('public')->delete($establecimiento->logo);
                            \Log::info('Logo anterior eliminado');
                        }

                        $path = $request->file('logo')->store('logos', 'public');
                        $establecimiento->logo = $path;
                        $establecimiento->save();

                        \Log::info('Nuevo logo guardado', ['path' => $path]);
                    } else {
                        \Log::info('No se envió nuevo logo');
                    }


                    // ✅ ACTUALIZAR HORARIOS
                    $diaSemanaMap = [
                        'Domingo' => 0,
                        'Lunes' => 1,
                        'Martes' => 2,
                        'Miércoles' => 3,
                        'Jueves' => 4,
                        'Viernes' => 5,
                        'Sábado' => 6,
                    ];

                    if ($request->filled('horarios')) {
                        \Log::info('Actualizando horarios del establecimiento');
                        $horarios = json_decode($request->horarios, true);

                        if (!is_array($horarios)) {
                            return response()->json(['message' => 'Formato de horarios inválido'], 422);
                        }

                        // Eliminar horarios anteriores
                        $establecimiento->horarios()->delete();

                        // Crear los nuevos horarios
                        foreach ($horarios as $h) {
                            if (!isset($diaSemanaMap[$h['dia']])) {
                                continue;
                            }

                            $diaSemana = $diaSemanaMap[$h['dia']];
                            $activo = $h['activo'] ?? false;
                            \Log::info('Valor activo recibido', ['activo' => $h['activo'], 'tipo' => gettype($h['activo'])]);

                            if ($activo && (empty($h['hora_inicio']) || empty($h['hora_fin']))) {
                                return response()->json([
                                    'message' => "Debe especificar hora_inicio y hora_fin para el día {$h['dia']} activado"
                                ], 422);
                            }

                            $establecimiento->horarios()->create([
                                'id_establecimiento' => $establecimiento->id_establecimiento,
                                'dia_semana' => $diaSemana,
                                'hora_inicio' => $activo ? $h['hora_inicio'] : null,
                                'hora_fin' => $activo ? $h['hora_fin'] : null,
                                'estado' => $activo ? 1 : 0,
                            ]);
                        }


                        \Log::info('Horarios actualizados correctamente');
                    }

                    $response = $this->successResponse($establecimiento, 'Establecimiento actualizado', 200);
                }
            }
        } catch (Exception $e) {
            \Log::error('Error en actualización de establecimiento', ['error' => $e->getMessage()]);
            $response = $this->errorResponse($e->getMessage(), null, 500);
        }

        return $response;
    }

    /**
     * Eliminar un establecimiento.
     */
    public function deleteEstablecimiento($id)
    {
        try {
            $establecimiento = Establecimiento::find($id);

            if (!$establecimiento) {
                return response()->json(['message' => 'Establecimiento no encontrado'], 404);
            }

            $establecimiento->delete();

            return $this->successResponse(null, 'Establecimiento eliminado correctamente', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    /**
     * Cambiar el estado de un establecimiento.
     */
    public function changeStatus($id)
    {
        try {
            \Log::info("Intentando cambiar estado del establecimiento", ['id' => $id]);

            $establecimiento = Establecimiento::find($id);
            if (!$establecimiento) {
                return response()->json(['message' => 'Establecimiento no encontrado'], 404);
            }

            $estadoAnterior = $establecimiento->estado;

            // Toggle de estado: null => 1, 1 => 0, 0 => 1
            $nuevoEstado = $estadoAnterior == 1 ? 0 : 1;
            $establecimiento->estado = $nuevoEstado;
            $establecimiento->save();

            \Log::info("Estado actualizado", [
                'id' => $id,
                'antes' => $estadoAnterior,
                'despues' => $nuevoEstado,
            ]);

            return response()->json([
                'message' => 'Estado actualizado',
                'nuevo_estado' => $nuevoEstado,
            ]);
        } catch (Exception $e) {
            \Log::error("Error al cambiar estado", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error al cambiar estado'], 500);
        }
    }

}
