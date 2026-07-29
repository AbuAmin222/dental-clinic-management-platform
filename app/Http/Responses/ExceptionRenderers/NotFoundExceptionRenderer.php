<?php

declare(strict_types=1);

namespace App\Http\Responses\ExceptionRenderers;

use App\Traits\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * Renders framework-level 404s (e.g. failed route-model binding) without
 * assuming any domain-exception contract.
 */
class NotFoundExceptionRenderer implements ExceptionRendererStrategy
{
    use ApiResponseTransformer;

    public function render(Throwable $exception): JsonResponse
    {
        return $this->errorResponse(
            'The requested resource could not be located.',
            404,
            null
        );
    }
}
