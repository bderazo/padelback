<?php

namespace App\Traits;

trait ApiResponseTrait
{
    /**
     * Genera una respuesta exitosa estandarizada
     *
     * @param mixed $data Los datos a devolver
     * @param string $message El mensaje descriptivo
     * @param int $statusCode El código HTTP de respuesta
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data = null, string $message = 'Operación exitosa', int $statusCode = 200)
    {
        return response()->json([
            'ok' => true,
            'data' => $data,
            'message' => $message
        ], $statusCode);
    }

    /**
     * Genera una respuesta de error estandarizada
     *
     * @param string $message El mensaje de error
     * @param mixed $data Datos adicionales sobre el error (opcional)
     * @param int $statusCode El código HTTP de error
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(string $message = 'Ha ocurrido un error', $data = null, int $statusCode = 400)
    {
        return response()->json([
            'ok' => false,
            'data' => $data,
            'message' => $message
        ], $statusCode);
    }
}