<?php

declare(strict_types=1);

namespace App\Contracts\Risk;

use App\Models\PaymentTransaction;
use App\ValueObjects\RiskAssessmentResult;

interface RiskInterceptorInterface
{
    public function assess(PaymentTransaction $transaction): RiskAssessmentResult;
}
