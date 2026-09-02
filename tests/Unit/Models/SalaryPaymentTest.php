<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Casts\MoneyCast;
use App\Enums\SalaryPaymentStatus;
use App\Models\Admin;
use App\Models\User;
use App\Models\Financial;
use App\Models\SalaryPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SalaryPaymentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function booted_saving_event_calculates_amount(): void
    {
        $user = User::factory()->create();
        Financial::factory()->create(['user_id' => $user->id]);

        $payment = SalaryPayment::create([
            'user_id' => $user->id,
            'processed_by_financial_id' => null,
            'base_amount' => 5000.0,
            'deduction_amount' => 500.0,
            'bonus_amount' => 250.0,
            'pay_period_start' => '2025-01-01',
            'pay_period_end' => '2025-01-31',
            'status' => SalaryPaymentStatus::Approved,
        ]);

        $payment = $payment->fresh();

        $this->assertSame(475000, (int) $payment->getRawOriginal('amount'));
    }

    #[Test]
    public function amount_is_base_minus_deduction_plus_bonus(): void
    {
        $user = User::factory()->create();

        $payment = SalaryPayment::create([
            'user_id' => $user->id,
            'base_amount' => 1000.0,
            'deduction_amount' => 200.0,
            'bonus_amount' => 100.0,
            'pay_period_start' => '2025-01-01',
            'pay_period_end' => '2025-01-31',
            'status' => SalaryPaymentStatus::Approved,
        ]);

        $this->assertSame(90000, (int) $payment->getRawOriginal('amount'));
    }

    #[Test]
    public function casts_all_money_fields_with_money_cast(): void
    {
        $user = User::factory()->create();
        $payment = SalaryPayment::create([
            'user_id' => $user->id,
            'base_amount' => 500.0,
            'deduction_amount' => 50.0,
            'bonus_amount' => 25.0,
            'pay_period_start' => '2025-01-01',
            'pay_period_end' => '2025-01-31',
            'status' => SalaryPaymentStatus::Pending,
        ]);

        $this->assertSame(500.0, $payment->base_amount);
        $this->assertSame(50.0, $payment->deduction_amount);
        $this->assertSame(25.0, $payment->bonus_amount);
        $this->assertSame(475.0, $payment->amount);
    }

    #[Test]
    public function casts_status_as_enum(): void
    {
        $user = User::factory()->create();
        $payment = SalaryPayment::create([
            'user_id' => $user->id,
            'base_amount' => 100000,
            'pay_period_start' => '2025-01-01',
            'pay_period_end' => '2025-01-31',
            'status' => SalaryPaymentStatus::Paid,
        ]);

        $this->assertEquals(SalaryPaymentStatus::Paid, $payment->status);
    }

    #[Test]
    public function uses_soft_deletes(): void
    {
        $user = User::factory()->create();
        $payment = SalaryPayment::create([
            'user_id' => $user->id,
            'base_amount' => 100000,
            'pay_period_start' => '2025-01-01',
            'pay_period_end' => '2025-01-31',
            'status' => SalaryPaymentStatus::Pending,
        ]);

        $payment->delete();

        $this->assertSoftDeleted($payment);
    }

    #[Test]
    public function fillable_attributes(): void
    {
        $fillable = (new SalaryPayment())->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('base_amount', $fillable);
        $this->assertContains('deduction_amount', $fillable);
        $this->assertContains('bonus_amount', $fillable);
        $this->assertContains('status', $fillable);
    }
}
