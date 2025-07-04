<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\ApiResponseTrait;
use Exception;

class SuperAdminController extends Controller
{
    use ApiResponseTrait;
    /**
     * Crear un nuevo establecimiento.
     */
    public function createEstablecimiento(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'ciudad' => 'required|string|max:100',
                'direccion' => 'required|string|max:255',
                'telefono_contacto' => 'nullable|string|max:20',
                'correo_contacto' => 'nullable|email|max:255',
            ]);

            $establecimiento = Establecimiento::create($validated);

            return $this->successResponse($establecimiento, 'Establecimiento creado', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    /**
     * Crear un usuario administrador (sin asignarlo aún).
     */
    public function createAdministrador(Request $request)
    {
        try {
            $validated = $request->validate([
                'username' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            $user = User::create([
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'admin',
                'id_establecimiento' => null,
            ]);

            return $this->successResponse($user, 'Administrador creado', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    /**
     * Asignar un usuario administrador a un establecimiento.
     */
    public function asignarAdministradorAEstablecimiento(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'id_establecimiento' => 'required|exists:establecimientos,id_establecimiento',
            ]);

            $user = User::find($validated['user_id']);

            if ($user->role !== 'admin') {
                return response()->json(['message' => 'El usuario no tiene rol de administrador'], 400);
            }

            $user->id_establecimiento = $validated['id_establecimiento'];
            $user->save();

            return $this->successResponse($user, 'Administrador asignado correctamente', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }
}
