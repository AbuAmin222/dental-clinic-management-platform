<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\BaseDomainException;
use App\Exceptions\BusinessRuleViolationException;
use App\Exceptions\IllegalInvoiceStateTransitionException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class IllegalInvoiceStateTransitionExceptionTest extends TestCase
{
    #[Test]
    public function extends_business_rule_violation_exception(): void
    {
        $exception = new IllegalInvoiceStateTransitionException();

        $this->assertInstanceOf(BusinessRuleViolationException::class, $exception);
        $this->assertInstanceOf(BaseDomainException::class, $exception);
    }

    #[Test]
    public function inherits_domain_code_and_status_code(): void
    {
        $exception = new IllegalInvoiceStateTransitionException('Cannot transition');

        $this->assertSame(1001, $exception->getDomainCode());
        $this->assertSame(422, $exception->getStatusCode());
        $this->assertSame('Cannot transition', $exception->getMessage());
    }
}
