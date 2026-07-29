<?php

namespace App\Exceptions;

use Exception;
// use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;

abstract class BaseDomainException extends Exception
{
    /**
     * Retrieve the distinct HTTP status code for the underlying exception.
     *
     * @return int
     */
    abstract public function getStatusCode(): int;

    /**
     * Retrieve specific error syntax details or validation matrices.
     *
     * @return mixed
     */
    abstract public function getErrorDetails(): mixed;


    /**
     * Abstract contract to force defining an application-specific failure code.
     */
    abstract public function getDomainCode(): int;
}
