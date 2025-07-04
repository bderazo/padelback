<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Error al confirmar reserva</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; text-align: center; padding: 50px; }
        .card { background: white; padding: 30px; border-radius: 10px; display: inline-block; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #dc3545; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Error al confirmar la reserva</h1>
        <p>{{ $mensaje ?? 'El enlace no es válido o la reserva ya ha sido confirmada.' }}</p>
    </div>
</body>
</html>
