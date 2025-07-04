<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\License;

class LicenseController extends Controller
{
    public function newOtp(Request $request)
    {
        $data = $request->validate([
            'pin' => 'required',
            'sport' => 'required',
            'duration' => 'required|in:7 días,15 días',
            'date_register' => 'required|date',
        ]);

        // Validar si el PIN ya existe
        if (License::where('pin', $data['pin'])->exists()) {
            return response()->json(['ok' => false, 'msg' => 'PIN ya registrado.'], 400);
        }

        License::create($data);

        return response()->json(['ok' => true, 'msg' => 'PIN registrado correctamente.']);
    }

    public function otpValidation(Request $request)
    {
        $data = $request->validate([
            'pin' => 'required',
            'user_name' => 'required',
            'date_use' => 'required|date',
            'description_use' => 'nullable'
        ]);
    
        $license = License::where('pin', $data['pin'])->first();
    
        if (!$license) {
            return response()->json(['ok' => false, 'msg' => 'PIN no encontrado.'], 404);
        }
    
        // ✅ Validar si ya fue usado
        if ($license->user_name !== null || $license->date_use !== null || $license->status === true) {
            return response()->json(['ok' => false, 'msg' => 'Este PIN ya está en uso.'], 400);
        }
    
        $license->update([
            'user_name' => $data['user_name'],
            'date_use' => $data['date_use'],
            'description_use' => $data['description_use'],
            'status' => true,
        ]);
        return response()->json([
            'ok' => true,
            'msg' => 'PIN validado correctamente.',
            'duration' => $license->duration,
            'sport' => $license->sport,
            'status' => $license->status,
            'user_name' => $license->user_name,
            'date_use' => $license->date_use,
            'description_use' => $license->description_use,
        ]);
    }

}
