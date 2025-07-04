<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionReserva extends Mailable
{
    use Queueable, SerializesModels;

    public $nombre;
    public $fecha;
    public $token;
    public $logo;

    /**
     * Create a new message instance.
     */
    public function __construct($nombre, $fecha, $token)
    {
        $this->nombre = $nombre;
        $this->fecha = $fecha;
        $this->token = $token;
        $this->logo = asset('images/arena.png');
        
        $this->withSwiftMessage(function ($message) {
            $message->setCharset('UTF-8');
        });
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmación de Reserva',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reserva', // <-- Aseg迆rate de tener esta vista creada
            with: [
                'nombre' => $this->nombre,
                'fecha' => $this->fecha,
                'token' => $this->token,
                'logo' => $this->logo,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
