<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EstablecimientoDeporte;

class EstablecimientoDeporteController extends Controller
{
    public function listByEstablecimiento($id)
    {
        $data = EstablecimientoDeporte::with('deporte')
            ->where('establecimiento_id', $id)
            ->get();

        return response()->json(['ok' => true, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'establecimiento_id' => 'required|exists:establecimientos,id_establecimiento',
            'deporte_id' => 'required|exists:deportes,id_deporte',
        ]);

        $exists = EstablecimientoDeporte::where('establecimiento_id', $validated['establecimiento_id'])
            ->where('deporte_id', $validated['deporte_id'])
            ->first();

        if ($exists) {
            return response()->json(['ok' => false, 'message' => 'Ya existe esa relación'], 409);
        }

        $relacion = EstablecimientoDeporte::create($validated);
        return response()->json(['ok' => true, 'data' => $relacion]);
    }

    public function update(Request $request, $id)
    {
        $relacion = EstablecimientoDeporte::find($id);
        if (!$relacion) {
            return response()->json(['ok' => false, 'message' => 'Relación no encontrada'], 404);
        }

        $validated = $request->validate([
            'precio' => 'sometimes|required|numeric|min:0',
            'duracion' => 'sometimes|required|integer|min:1',
        ]);

        $relacion->update($validated);
        return response()->json(['ok' => true, 'data' => $relacion]);
    }

    public function destroy($id)
    {
        $relacion = EstablecimientoDeporte::find($id);
        if (!$relacion) {
            return response()->json(['ok' => false, 'message' => 'Relación no encontrada'], 404);
        }

        $relacion->delete();
        return response()->json(['ok' => true, 'message' => 'Relación eliminada']);
    }
}
