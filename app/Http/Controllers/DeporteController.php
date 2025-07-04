<?php

namespace App\Http\Controllers;

use App\Models\Deporte;
use Illuminate\Http\Request;

class DeporteController extends Controller
{
    public function index()
    {
        return response()->json([
            'ok' => true,
            'data' => Deporte::all(),
        ]);
    }

    public function show($id)
    {
        $deporte = Deporte::find($id);

        if (!$deporte) {
            return response()->json(['ok' => false, 'message' => 'Deporte no encontrado'], 404);
        }

        return response()->json(['ok' => true, 'data' => $deporte]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_base' => 'required|numeric|min:0',
            'duracion_base' => 'required|integer|min:1',
        ]);

        $deporte = Deporte::create($validated);

        return response()->json(['ok' => true, 'data' => $deporte], 201);
    }

    public function update(Request $request, $id)
    {
        $deporte = Deporte::find($id);

        if (!$deporte) {
            return response()->json(['ok' => false, 'message' => 'Deporte no encontrado'], 404);
        }

        $validated = $request->validate([
            'titulo' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_base' => 'sometimes|required|numeric|min:0',
            'duracion_base' => 'sometimes|required|integer|min:1',
        ]);

        $deporte->update($validated);

        return response()->json(['ok' => true, 'data' => $deporte]);
    }

    public function destroy($id)
    {
        $deporte = Deporte::find($id);

        if (!$deporte) {
            return response()->json(['ok' => false, 'message' => 'Deporte no encontrado'], 404);
        }

        $deporte->delete();

        return response()->json(['ok' => true, 'message' => 'Deporte eliminado']);
    }
}
