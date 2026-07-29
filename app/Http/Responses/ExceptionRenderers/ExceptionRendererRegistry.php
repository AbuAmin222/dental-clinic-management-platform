<?php

namespace App\Http\Responses\ExceptionRenderers;

use Throwable;
use App\Exceptions\BaseDomainException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionRendererRegistry
{
    /**
     * Structural strategy lookup map linking exception classes to architectural renderers.
     * @var array<string, string>
     */
    private static array $registry = [
        BaseDomainException::class       => DomainExceptionRenderer::class,
        ValidationException::class       => ValidationExceptionRenderer::class,
        NotFoundHttpException::class     => NotFoundExceptionRenderer::class,
    ];

    /**
     * Resolves dynamically the matching structural Strategy execution layout.
     *
     * @param Throwable $exception
     * @return ExceptionRendererStrategy
     */
    public static function resolve(Throwable $exception): ExceptionRendererStrategy
    {
        foreach (self::$registry as $exceptionClass => $rendererClass) {
            if ($exception instanceof $exceptionClass) {
                return new $rendererClass();
            }
        }

        return new FallbackExceptionRenderer();
    }
}
