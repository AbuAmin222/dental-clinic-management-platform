<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Casts\MoneyCast;
use App\Models\Financial;
use App\Models\FinancialAuditLog;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FinancialAuditLogTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function casts_amount_changed_with_money_cast(): void
    {
        $financial = Financial::factory()->create();
        $log = FinancialAuditLog::create([
            'financial_id' => $financial->id,
            'action' => 'invoice_issued',
            'amount_changed' => 500.0,
        ]);

        $this->assertSame(500.0, $log->amount_changed);
    }

    #[Test]
    public function casts_payload_fields_as_array(): void
    {
        $financial = Financial::factory()->create();
        $log = FinancialAuditLog::create([
            'financial_id' => $financial->id,
            'action' => 'test',
            'payload_before' => ['status' => 'draft'],
            'payload_after' => ['status' => 'pending'],
        ]);

        $this->assertIsArray($log->payload_before);
        $this->assertSame('draft', $log->payload_before['status']);
        $this->assertIsArray($log->payload_after);
        $this->assertSame('pending', $log->payload_after['status']);
    }

    #[Test]
    public function updated_at_is_null(): void
    {
        $this->assertNull(FinancialAuditLog::UPDATED_AT);
    }

    #[Test]
    public function updating_throws_logic_exception(): void
    {
        $financial = Financial::factory()->create();
        $log = FinancialAuditLog::create([
            'financial_id' => $financial->id,
            'action' => 'test',
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('immutable and cannot be updated');

        $log->update(['action' => 'modified']);
    }

    #[Test]
    public function deleting_throws_logic_exception(): void
    {
        $financial = Financial::factory()->create();
        $log = FinancialAuditLog::create([
            'financial_id' => $financial->id,
            'action' => 'test',
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('immutable and cannot be deleted');

        $log->delete();
    }

    #[Test]
    public function fillable_attributes(): void
    {
        $fillable = (new FinancialAuditLog())->getFillable();

        $this->assertContains('financial_id', $fillable);
        $this->assertContains('invoice_id', $fillable);
        $this->assertContains('action', $fillable);
        $this->assertContains('amount_changed', $fillable);
        $this->assertContains('payload_before', $fillable);
        $this->assertContains('payload_after', $fillable);
        $this->assertContains('ip_address', $fillable);
    }

    #[Test]
    public function financial_relationship(): void
    {
        $financial = Financial::factory()->create();
        $log = FinancialAuditLog::create([
            'financial_id' => $financial->id,
            'action' => 'test',
        ]);

        $this->assertInstanceOf(Financial::class, $log->financial);
    }

    #[Test]
    public function invoice_relationship(): void
    {
        $financial = Financial::factory()->create();
        $invoice = Invoice::factory()->create();
        $log = FinancialAuditLog::create([
            'financial_id' => $financial->id,
            'invoice_id' => $invoice->id,
            'action' => 'test',
        ]);

        $this->assertInstanceOf(Invoice::class, $log->invoice);
    }
}
