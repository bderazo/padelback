<?php

namespace App\Services;

use App\Models\Cliente;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificacionReserva;

class MailService
{
    public function sendConfirmationMail($id_cliente, $fecha, $hora_inicio, $hora_fin, $reserva_id)
    {
        $cliente = Cliente::with('user')->findOrFail($id_cliente);

        $nombre = $cliente->nombre;
        $email = $cliente->user->email;

        // Generar un token único basado en el ID de la reserva
        $secret = config('app.key');
        $token = hash_hmac('sha256', $reserva_id, $secret);

        // Lógica para guardar token si deseas verificarlo más adelante (opcional)

        $datetime = "$fecha $hora_inicio - $hora_fin";
        try {
            Mail::to($email)->send(
                new NotificacionReserva($nombre, $datetime, $token)
            );
            \Log::info("Correo enviado a: {$email} con token: {$token}");
        } catch (\Exception $e) {
            \Log::error("Error al enviar correo a {$email}: " . $e->getMessage());
            \Log::error("Trace: " . $e->getTraceAsString());
        }
    }
}
