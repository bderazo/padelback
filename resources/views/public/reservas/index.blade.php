<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservas Pendientes</title>
    <style>
        body { font-family: sans-serif; background: #f3f3f3; padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #eee; }
        .btn { padding: 5px 10px; background: green; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <h2>Reservas Pendientes</h2>
    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif
    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Cancha</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reservas as $reserva)
                <tr>
                    <td>{{ $reserva->cliente->nombre }}</td>
                    <td>{{ $reserva->cancha->nombre }}</td>
                    <td>{{ $reserva->fecha_reserva }}</td>
                    <td>{{ $reserva->hora_inicio }} - {{ $reserva->hora_fin }}</td>
                    <td>
                        <a class="btn" href="{{ route('reserva.confirmar', $reserva->id_reserva) }}">Confirmar</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No hay reservas pendientes.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
