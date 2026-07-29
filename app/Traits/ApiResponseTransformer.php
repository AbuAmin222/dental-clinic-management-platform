<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Reusable controller trait providing immutable, highly standardized HTTP JSON response structures.
 */
trait ApiResponseTransformer
{
    /**
     * Structure a standardized immutable success response payload.
     *
     * @param mixed  $data        The primary response data payload.
     * @param string $message     Descriptive operational success message.
     * @param int    $code        HTTP status code (Defaults to 200 OK).
     * @return       JsonResponse Standardized JSON response object.
     */
    protected function successResponse(mixed $data, string $message = 'Operation completed successfully.', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status'  => $code,
            'message' => $message,
            'data'    => $data,
            'errors'  => null,
        ], $code);
    }

    /**
     * Structure a standardized immutable error response payload.
     *
     * @param string    $message        Human-readable error explanation.
     * @param int       $code           HTTP error status code.
     * @param mixed     $errors         Detailed validation or structural error payload.
     * @param int|null  $domainCode     Optional internal business-domain error code, distinct from HTTP status.
     * @return          JsonResponse    Standardized JSON response object.
     */
    protected function errorResponse(string $message, int $code, mixed $errors = null, ?int $domainCode = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'status'  => $code,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
            'domain_code' => $domainCode,

        ], $code);
    }
}
