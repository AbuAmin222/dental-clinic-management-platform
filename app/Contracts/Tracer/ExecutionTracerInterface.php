<?php

declare(strict_types=1);

namespace App\Contracts\Tracer;

interface ExecutionTracerInterface
{
    /**
     * Trace execution reach by logging an attribute/marker.
     *
     * @param string $attribute
     * @param array $context
     * @return void
     */
    public function mark(string $attribute, array $context = []): void;
}
