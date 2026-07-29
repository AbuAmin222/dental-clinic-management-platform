<?php

namespace App\Exceptions;

class BusinessRuleViolationException extends BaseDomainException
{
    protected mixed $errors;

    /**
     * @param string $message
     * @param mixed|null $errors
     */
    public function __construct(string $message = "Business constraint violation occurred.", mixed $errors = null)
    {
        parent::__construct($message);
        $this->errors = $errors;
    }
    public function getDomainCode(): int
    {
        return 1001;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return 422;
    }

    /**
     * @return mixed
     */
    public function getErrorDetails(): mixed
    {
        return $this->errors;
    }
}
