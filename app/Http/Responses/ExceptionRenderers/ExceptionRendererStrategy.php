<?php

namespace App\Http\Responses\ExceptionRenderers;

use Throwable;
use Illuminate\Http\JsonResponse;

interface ExceptionRendererStrategy
{
    /**
     * Render the given exception into a standardized HTTP JSON representation.
     *
     * @param Throwable $exception
     * @return JsonResponse
     */
    public function render(Throwable $exception): JsonResponse;
}
