<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\BaseDomainException;
use App\Exceptions\ResourceNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ResourceNotFoundExceptionTest extends TestCase
{
    #[Test]
    public function extends_base_domain_exception(): void
    {
        $exception = new ResourceNotFoundException();

        $this->assertInstanceOf(BaseDomainException::class, $exception);
    }

    #[Test]
    public function default_message_is_empty(): void
    {
        $exception = new ResourceNotFoundException();

        $this->assertSame('', $exception->getMessage());
    }

    #[Test]
    public function custom_message_can_be_set(): void
    {
        $exception = new ResourceNotFoundException('Resource not found');

        $this->assertSame('Resource not found', $exception->getMessage());
    }

    #[Test]
    public function get_domain_code_returns_1000(): void
    {
        $exception = new ResourceNotFoundException();

        $this->assertSame(1000, $exception->getDomainCode());
    }

    #[Test]
    public function get_status_code_returns_404(): void
    {
        $exception = new ResourceNotFoundException();

        $this->assertSame(404, $exception->getStatusCode());
    }

    #[Test]
    public function get_error_details_returns_null(): void
    {
        $exception = new ResourceNotFoundException();

        $this->assertNull($exception->getErrorDetails());
    }
}
