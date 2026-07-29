<?php

namespace App\Exceptions;

class ResourceNotFoundException extends BaseDomainException
{
    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return 404;
    }

    public function getDomainCode(): int
    {
        return 1000;
    }

    public function getErrorDetails(): mixed
    {
        return null;
    }
}
