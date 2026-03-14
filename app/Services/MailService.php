<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\User;
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

    public function sendPasswordResetMail($email)
    {
        try {
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                Log::warning("Intento de recuperación para email no registrado: {$email}");
                return false;
            }

            // Generar token seguro
            $token = Str::random(60);
            $hashedToken = hash('sha256', $token);
            
            // Guardar token en la base de datos (validez de 60 minutos)
            DB::table('password_resets')->updateOrInsert(
                ['email' => $user->email],
                ['token' => $hashedToken, 'created_at' => Carbon::now()]
            );

            // Construir URL segura
            $resetUrl = config('app.frontend_url').'/reset-password?'.http_build_query([
                'token' => $token,
                'email' => $user->email
            ]);

            // Enviar correo (mismo patrón que en sendConfirmationMail)
            Mail::to($user->email)->send(new PasswordResetMail($resetUrl));
            
            Log::info("Correo de recuperación enviado a: {$user->email}");
            return true;

        } catch (\Exception $e) {
            Log::error("Error enviando correo de recuperación a {$email}: " . $e->getMessage());
            Log::error("Trace: " . $e->getTraceAsString());
            return false;
        }
    }
}
