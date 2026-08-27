<?php

declare(strict_types=1);

use App\Contracts\Tracer\ExecutionTracerInterface;

if (! function_exists('trace_reach')) {
    /**
     * Helper to trace execution path across system methods.
     *
     * @param string $attribute Marker name or method identifier
     * @param array $context Data to attach for debugging
     * @return void
     */
    function trace_reach(string $attribute, array $context = []): void
    {
        app(ExecutionTracerInterface::class)->mark($attribute, $context);
    }
}
