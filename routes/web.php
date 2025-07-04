<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservaPublicaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return response()->json([
        'code' => 200,
        'status' => 'success',
        'message' => 'Servidor API funcionando correctamente',
    ], 200);
});

Route::get('/reservas/pendientes', [ReservaPublicaController::class, 'index']);
Route::get('/reservas/{id}/confirmar', [ReservaPublicaController::class, 'confirmar'])->name('reserva.confirmar');

Route::get('/admin', function () {
    return file_get_contents(public_path('dashboard/index.html'));
});
