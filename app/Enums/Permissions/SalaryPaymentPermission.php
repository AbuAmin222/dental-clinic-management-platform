<?php

declare(strict_types=1);

namespace App\Enums\Permissions;

use App\Contracts\Permissions\PermissionEnum;

enum SalaryPaymentPermission: string implements PermissionEnum
{
    case Record   = 'salary_payments.record';
    case Approve  = 'salary_payments.approve';
    case Hold     = 'salary_payments.hold';
    case Reject   = 'salary_payments.reject';
    case Cancel   = 'salary_payments.cancel';
    case MarkPaid = 'salary_payments.markPaid';

    public function label(): string
    {
        return match ($this) {
            self::Record   => 'RECORD SALARY PAYMENT',
            self::Approve  => 'APPROVE SALARY PAYMENT',
            self::Hold     => 'HOLD SALARY PAYMENT',
            self::Reject   => 'REJECT SALARY PAYMENT',
            self::Cancel   => 'CANCEL SALARY PAYMENT',
            self::MarkPaid => 'CONFIRM ACTUAL SALARY DISBURSEMENT',
        };
    }

    public function group(): string
    {
        return 'salary_payments';
    }
}
