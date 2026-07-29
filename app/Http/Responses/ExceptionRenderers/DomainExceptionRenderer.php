<?php

namespace App\Http\Responses\ExceptionRenderers;

use Throwable;
use App\Exceptions\BaseDomainException;
use App\Traits\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;

class DomainExceptionRenderer implements ExceptionRendererStrategy
{
    use ApiResponseTransformer;

    /**
     * Maps Custom Domain Exceptions cleanly to structural API definitions.
     */
    public function render(Throwable $exception): JsonResponse
    {
        /** @var BaseDomainException $exception */
        return $this->errorResponse(
            // $exception->getMessage(),
            $exception->getStatusCode(),
            $exception->getErrorDetails(),
            $exception->getDomainCode()
        );
    }
}
