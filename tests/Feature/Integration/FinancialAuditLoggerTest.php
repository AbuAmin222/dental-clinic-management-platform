<?php

declare(strict_types=1);

namespace Tests\Feature\Integration;

use App\Models\Invoice;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Services\PaymentService\FinancialAuditLogger;
use App\Models\Financial;
use App\Models\FinancialAuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FinancialAuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    private FinancialAuditLogger $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->logger = new FinancialAuditLogger();
    }

    #[Test]
    public function log_creates_audit_entry(): void
    {
        $financial = Financial::factory()->create();
        $invoice = Invoice::factory()->create();
        $user = $financial->user;

        $log = $this->logger->log(
            actor: $user,
            action: 'invoice_issued',
            invoice: $invoice,
            before: ['status' => 'draft'],
            after: ['status' => 'pending'],
            ip: '127.0.0.1'
        );

        $this->assertInstanceOf(FinancialAuditLog::class, $log);
        $this->assertDatabaseHas('financial_audit_logs', [
            'financial_id' => $financial->id,
            'invoice_id' => $invoice->id,
            'action' => 'invoice_issued',
            'ip_address' => '127.0.0.1',
        ]);
    }

    #[Test]
    public function log_with_null_invoice(): void
    {
        $financial = Financial::factory()->create();
        $user = $financial->user;

        $log = $this->logger->log(
            actor: $user,
            action: 'bulk_operation'
        );

        $this->assertDatabaseHas('financial_audit_logs', [
            'financial_id' => $financial->id,
            'action' => 'bulk_operation',
            'invoice_id' => null,
        ]);
    }

    #[Test]
    public function log_with_amount_changed(): void
    {
        $financial = Financial::factory()->create();
        $user = $financial->user;
        $invoice = Invoice::factory()->create();

        $log = $this->logger->log(
            actor: $user,
            action: 'payment_recorded',
            invoice: $invoice,
            amountChanged: 150.50
        );

        $this->assertSame(15050, $log->getRawOriginal('amount_changed'));
    }
}
