<?php

namespace App\Http\Responses\ExceptionRenderers;

use Throwable;
use Illuminate\Validation\ValidationException;
use App\Traits\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class ValidationExceptionRenderer implements ExceptionRendererStrategy
{
    use ApiResponseTransformer;

    /**
     * Transforms native Framework Validation exceptions into unified JSON definitions.
     */
    public function render(Throwable $exception): JsonResponse
    {
        /** @var ValidationException $exception */
        return $this->errorResponse(
            'The provided data payload failed structural validation checks.',
            422,
            $exception->errors()
        );
    }
}
