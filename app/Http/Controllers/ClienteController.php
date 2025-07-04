<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Exception;

class ClienteController extends Controller
{

    use ApiResponseTrait;

    /**
     * Obtener cliente por ID de usuario.
     */
    public function getClientByUserId($userId)
    {
        try {
            $user = User::with('cliente')->find($userId);

            if (!$user || !$user->cliente) {
                return response()->json(['message' => 'Cliente no encontrado'], 404);
            }

            return $this->successResponse([
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'client_id' => $user->cliente->id_cliente,
                'nombre' => $user->cliente->nombre,
                'telefono' => $user->cliente->telefono,
                'direccion' => $user->cliente->direccion,
                'cedula' => $user->cliente->cedula,
            ], 'Cliente encontrado', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    /**
     * Obtener cliente por cédula.
     */
    public function getClienteByCedula($cedula)
    {
        try {
            $cliente = Cliente::where('cedula', $cedula)->with('user')->first();

            if (!$cliente || !$cliente->user) {
                return response()->json(['message' => 'Cliente no encontrado'], 404);
            }

            return $this->successResponse([
                'user_id' => $cliente->user->id,
                'username' => $cliente->user->username,
                'email' => $cliente->user->email,
                'client_id' => $cliente->id_cliente,
                'nombre' => $cliente->nombre,
                'telefono' => $cliente->telefono,
                'direccion' => $cliente->direccion,
                'cedula' => $cliente->cedula,
            ], 'Cliente encontrado', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    /**
     * Listar todos los clientes.
     */
    public function listClients()
    {
        try {
            $clientes = Cliente::with('user')->get();

            $response = $clientes->map(function ($cliente) {
                return [
                    'user_id' => $cliente->user->id,
                    'username' => $cliente->user->username,
                    'email' => $cliente->user->email,
                    'id_cliente' => $cliente->id_cliente,
                    'nombre' => $cliente->nombre,
                    'telefono' => $cliente->telefono,
                    'direccion' => $cliente->direccion,
                    'cedula' => $cliente->cedula,
                    'role' => $cliente->user->role,
                    'id_establecimiento' => $cliente->user->id_establecimiento,
                ];
            });

            return $this->successResponse($response, 'Clientes obtenidos', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }
}
