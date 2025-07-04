<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cliente;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Traits\ApiResponseTrait;
use Exception;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function register(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|unique:users,email',
                'username' => 'required|unique:users,username',
                'password' => 'required|min:6|confirmed',
                'nombre' => 'required|string|max:255',
                'telefono' => 'nullable|string|max:20',
                'direccion' => 'nullable|string|max:255',
                'cedula' => 'required|string|max:20|unique:clientes,cedula',
                'role' => 'nullable|in:cliente,admin,superadmin', // Validar rol si se proporciona
            ]);

            // Crear el usuario
            $user = User::create([
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'password_confirmation' => Hash::make($request->input('password')),
                'role' => $request->input('role', 'cliente'), // Asignar rol por defecto
            ]);

            // Crear el cliente y asociarlo al usuario
            $cliente = Cliente::create([
                'user_id' => $user->id,
                'nombre' => $request->input('nombre'),
                'telefono' => $request->input('telefono'),
                'direccion' => $request->input('direccion'),
                'cedula' => $request->input('cedula'),
            ]);

            $data = [
                'token' => $user->createToken('auth_token')->plainTextToken,
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'client_id' => $cliente->id_cliente,
                'nombre' => $cliente->nombre,
                'telefono' => $cliente->telefono,
                'direccion' => $cliente->direccion,
                'cedula' => $cliente->cedula,
            ];

            return $this->successResponse($data, 'Usuario registrado con éxito', 201);
        } catch (ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }

    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $user = User::where('email', $request->email)->with('cliente')->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Las credenciales son incorrectas.'],
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'token' => $token,
                'user' => $user
            ], 'Acceso correcto', 201);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }


    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return $this->successResponse(null, 'Sesión cerrada correctamente', 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    // UserController.php
    public function getAdmins()
    {
        $admins = User::where('role', 'admin')->get(['id', 'username', 'email', 'role']);
        return response()->json($admins);
    }

}
