<?php

namespace App\Http\Responses\ExceptionRenderers;


use Throwable;
use App\Traits\ApiResponseTransformer;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class FallbackExceptionRenderer implements ExceptionRendererStrategy
{
    use ApiResponseTransformer;

    /**
     * Encapsulates unexpected runtime server crashes protecting infrastructure leaks.
     */
    public function render(Throwable $exception): JsonResponse
    {
        $status = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : 500;

        $message = config('app.debug')
            ? $exception->getMessage()
            : $this->defaultMessageFor($status);

        $errors = config('app.debug') ? $exception->getTrace() : null;

        return $this->errorResponse($message, $status, $errors);
    }

    private function defaultMessageFor(int $status): string
    {
        return match ($status) {
            419 => 'Your session has expired. Please refresh the page and try again.',
            403 => 'You do not have permission to perform this action.',
            429 => 'Too many requests. Please wait a moment and try again.',
            default => 'A fatal server infraction occurred.',
        };
    }
}
