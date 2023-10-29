<?php

namespace App\Helpers;

class ResponseHelper
{
    public static function success($data, $message = null, $code = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function error($code, $message = null): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => null,
        ], $code);
    }
}
