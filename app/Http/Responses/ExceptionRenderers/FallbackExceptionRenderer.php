<?php

namespace App\Http\Responses\ExceptionRenderers;

use Throwable;
use App\Traits\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class FallbackExceptionRenderer implements ExceptionRendererStrategy
{
    use ApiResponseTransformer;

    /**
     * Encapsulates unexpected runtime server crashes protecting infrastructure leaks.
     */
    public function render(Throwable $exception): JsonResponse
    {
        $message = config('app.debug') ? $exception->getMessage() : 'A fatal server infraction occurred.';
        $errors = config('app.debug') ? $exception->getTrace() : null;

        return $this->errorResponse(
            $message,
            500,
            $errors
        );
    }
}
