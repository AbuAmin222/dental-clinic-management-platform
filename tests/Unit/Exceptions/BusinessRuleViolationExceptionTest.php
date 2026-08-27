<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\BaseDomainException;
use App\Exceptions\BusinessRuleViolationException;
use App\Exceptions\ResourceNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BusinessRuleViolationExceptionTest extends TestCase
{
    #[Test]
    public function extends_base_domain_exception(): void
    {
        $exception = new BusinessRuleViolationException();

        $this->assertInstanceOf(BaseDomainException::class, $exception);
    }

    #[Test]
    public function default_message_is_set(): void
    {
        $exception = new BusinessRuleViolationException();

        $this->assertSame('Business constraint violation occurred.', $exception->getMessage());
    }

    #[Test]
    public function custom_message_can_be_set(): void
    {
        $exception = new BusinessRuleViolationException('Custom business error');

        $this->assertSame('Custom business error', $exception->getMessage());
    }

    #[Test]
    public function get_domain_code_returns_1001(): void
    {
        $exception = new BusinessRuleViolationException();

        $this->assertSame(1001, $exception->getDomainCode());
    }

    #[Test]
    public function get_status_code_returns_422(): void
    {
        $exception = new BusinessRuleViolationException();

        $this->assertSame(422, $exception->getStatusCode());
    }

    #[Test]
    public function get_error_details_returns_errors_when_provided(): void
    {
        $errors = ['field' => ['The field is required.']];
        $exception = new BusinessRuleViolationException('Error', $errors);

        $this->assertSame($errors, $exception->getErrorDetails());
    }

    #[Test]
    public function get_error_details_returns_null_when_not_provided(): void
    {
        $exception = new BusinessRuleViolationException();

        $this->assertNull($exception->getErrorDetails());
    }
}
