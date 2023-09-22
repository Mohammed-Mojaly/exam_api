<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success($data = null, $message = 'Operation successful', $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function error($message = 'An error occurred', $code = 400, $data = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function badRequest($message = 'Bad Request', $data = null): JsonResponse
    {
        return self::error($message, 400, $data);
    }

    public static function unauthorized($message = 'Unauthorized', $data = null): JsonResponse
    {
        return self::error($message, 401, $data);
    }

    public static function forbidden($message = 'Forbidden', $data = null): JsonResponse
    {
        return self::error($message, 403, $data);
    }

    public static function notFound($message = 'Not Found', $data = null): JsonResponse
    {
        return self::error($message, 404, $data);
    }

    public static function unprocessableEntity($message = 'Unprocessable Entity', $data = null): JsonResponse
    {
        return self::error($message, 422, $data);
    }

    public static function serverError($message = 'Server Error', $data = null): JsonResponse
    {
        return self::error($message, 500, $data);
    }
}
