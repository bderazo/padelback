<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CanchaController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\EstablecimientoController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\DeporteController;
use App\Http\Controllers\EstablecimientoDeporteController;
use App\Http\Controllers\WhatsappController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::get('/admins', [AuthController::class, 'getAdmins']);


    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::post('/logout', [AuthController::class, 'logout']);
    });

});

// RUTAS CLIENTE
Route::middleware('auth:sanctum')->prefix('clientes')->group(function () {
    Route::get('/', [ClienteController::class, 'listClients']);
    Route::get('/user/{userId}', [ClienteController::class, 'getClientByUserId']);
    Route::get('/cedula/{cedula}', [ClienteController::class, 'getClienteByCedula']);
});

// RUTAS CANCHAS NO AUTH
Route::prefix('canchas')->group(function () {
    Route::get('/disponible', [CanchaController::class, 'listAvailableCanchas']);
    Route::get('/establecimiento/{idEstablecimiento}', [CanchaController::class, 'getCanchasByEstablecimientoId']);
    Route::get('/', [CanchaController::class, 'listCanchas']);
    Route::get('/{id}', [CanchaController::class, 'getCanchaById']);
    Route::get('{id}/disponibilidad', [CanchaController::class, 'getFechasDisponibles']);
    Route::get('/{id}/horarios-disponibles', [CanchaController::class, 'getHorariosDisponiblesPorFecha']);
});


// RUTAS CANCHAS AUTH
Route::prefix('canchas')->middleware(['auth:sanctum', 'role:admin,superadmin'])->group(function () {
    // Nuevos métodos protegidos por auth
    Route::post('/', [CanchaController::class, 'createCancha']);
    Route::post('/createCanchas', [CanchaController::class, 'bulkCreateCanchas']);
    Route::post('/estado/{id}', [CanchaController::class, 'changeStatus']);
    Route::post('/{id}/imagen', [CanchaController::class, 'uploadCanchaImagen']);
    Route::match(['POST', 'PUT'], '/{id}', [CanchaController::class, 'updateCancha']);

});


// RUTAS ESTABLECIMIENTOS NO AUTH
Route::prefix('establecimientos')->group(function () {
    Route::get('/', [EstablecimientoController::class, 'listEstablecimientos']);
    Route::get('/{id}', [EstablecimientoController::class, 'getEstablecimientoById']);
});

// RUTAS ESTABLECIMIENTOS AUTH
Route::prefix('establecimientos')->middleware('auth:sanctum', 'role:superadmin')->group(function () {
    Route::post('/', [EstablecimientoController::class, 'createEstablecimiento']);
    Route::match(['POST', 'PUT'], '/{id}', [EstablecimientoController::class, 'updateEstablecimiento']);
    Route::delete('/{id}', [EstablecimientoController::class, 'deleteEstablecimiento']);
    Route::post('/{id}/logo', [EstablecimientoController::class, 'updateLogo']);
    Route::put('/{id}/estado', [EstablecimientoController::class, 'changeStatus']);
});

// RUTAS RESERVAS NO AUTH
Route::prefix('reservas')->group(function () {
    Route::get('/', [ReservaController::class, 'listReservas']);
    Route::get('/{id}', [ReservaController::class, 'getReservaById']);
    Route::get('/calcular-monto', [ReservaController::class, 'calcularMonto']);
    Route::get('/confirmar-reserva/{token}', [ReservaController::class, 'confirmarReserva']);
    Route::post('/reservas/canchas-disponibles', [ReservaController::class, 'canchasDisponibles']);
    Route::get('/cliente/{id}', [ReservaController::class, 'reservasPorCliente']);
    Route::get('/establecimiento/{id}', [ReservaController::class, 'reservasPorEstablecimiento']);

});

// RUTAS RESERVAS AUTH
Route::prefix('reservas')->middleware('auth:sanctum')->group(function () {
    Route::post('/', [ReservaController::class, 'createReserva']);
    Route::put('/{id}', [ReservaController::class, 'updateReserva']);
    Route::post('/{id}/cancel', [ReservaController::class, 'cancelReserva']);
    Route::put('/{id}/confirmar', [ReservaController::class, 'confirmar']);
    Route::put('/{id}/cancelar', [ReservaController::class, 'cancelar']);
    Route::get('/historial', [ReservaController::class, 'listHistorialReservas']);
    Route::get('/proximas', [ReservaController::class, 'listProximasReservas']);
});


// RUTAS SUPERADMIN
Route::prefix('superadmin')->middleware(['auth:sanctum', 'role:superadmin'])->group(function () {
    Route::post('/establecimientos', [SuperAdminController::class, 'createEstablecimiento']);
    Route::post('/administradores', [SuperAdminController::class, 'createAdministrador']);
    Route::post('/asignar-administrador', [SuperAdminController::class, 'asignarAdministradorAEstablecimiento']);
});

// RUTAS ADMIN
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/', function () {
        return 'Solo para admins';
    });
});

// RUTAS CLIENTE
Route::middleware(['auth:sanctum', 'role:cliente'])->prefix('client')->group(function () {
    Route::get('/', function () {
        return 'Solo para clientes';
    });
});

// RUTAS DEPORTES (solo para superadmin o admin)
Route::prefix('deportes')->middleware(['auth:sanctum', 'role:admin,superadmin,cliente'])->group(function () {
    Route::get('/', [DeporteController::class, 'index']);
    Route::get('/{id}', [DeporteController::class, 'show']);
    Route::post('/', [DeporteController::class, 'store']);
    Route::match(['POST', 'PUT'], '/{id}', [DeporteController::class, 'update']);
    Route::delete('/{id}', [DeporteController::class, 'destroy']);
});

// RUTAS ESTABLECIMIENTO-DEPORTES (solo admin o superadmin)
Route::prefix('establecimiento-deportes')->middleware(['auth:sanctum', 'role:admin,superadmin,cliente'])->group(function () {
    Route::get('/{establecimiento_id}', [EstablecimientoDeporteController::class, 'listByEstablecimiento']);
    Route::post('/', [EstablecimientoDeporteController::class, 'store']);
    Route::put('/{id}', [EstablecimientoDeporteController::class, 'update']);
    Route::delete('/{id}', [EstablecimientoDeporteController::class, 'destroy']);
});



// RUTAS LICENSE
Route::post('/newOtp', [LicenseController::class, 'newOtp']);
Route::post('/otpValidation', [LicenseController::class, 'otpValidation']);

// Ruta Whatsapp
Route::post('/send-whatsapp', [WhatsappController::class, 'send']);