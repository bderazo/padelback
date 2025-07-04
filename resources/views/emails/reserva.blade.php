<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reserva Confirmada</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f7f7f7; padding: 20px;">
    <div
        style="max-width: 600px; margin: auto; background: white; border-radius: 8px; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">

        @if (!empty($logo))
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="{{ $logo }}" alt="Logo" style="width: 100%; height: auto; display: block;" />
            </div>
        @endif

        <p style="color: #555;">Hola {{ $nombre }},</p>

        <p style="color: #555;">
            Hemos registrado tu reserva para el día <strong>{{ $fecha }}</strong>. No necesitas realizar ninguna acción adicional.
        </p>

        <div style="text-align: center; margin: 30px 0;">
            <h2 style="color: #333;">¡Gracias por reservar con nosotros!</h2>
        </div>

        <p style="color: #999; font-size: 12px;">Si no realizaste esta reserva, por favor contáctanos o ignora este mensaje.</p>
    </div>
</body>

</html>
