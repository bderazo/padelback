<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Reserva;

class ReservaConfirmada extends Mailable
{
    use Queueable, SerializesModels;

    public $reserva;
    public $logo;


    public function __construct(Reserva $reserva, ?string $logo = null)
    {
        $this->reserva = $reserva;
        $this->logo = asset('images/arena.png');
    }

    public function build()
    {
        return $this->subject('Tu reserva fue confirmada')
            ->view('emails.reserva_confirmada')
            ->with([
                'reserva' => $this->reserva,
                'logo' => $this->logo
            ]);
    }
}
