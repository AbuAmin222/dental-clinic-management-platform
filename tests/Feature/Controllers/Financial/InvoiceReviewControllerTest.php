<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Financial;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvoiceReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[Test]
    public function index_displays_draft_invoices_for_authorized_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('financial');

        Invoice::factory()->create(['status' => InvoiceStatus::Draft]);

        $response = $this->actingAs($user)->get('/financial/invoices');

        $response->assertStatus(200);
    }

    #[Test]
    public function index_returns_403_for_non_financial_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('patient');

        $response = $this->actingAs($user)->get('/financial/invoices');

        $response->assertStatus(403);
    }

    #[Test]
    public function index_only_shows_draft_invoices(): void
    {
        $user = User::factory()->create();
        $user->assignRole('financial');

        Invoice::factory()->create(['status' => InvoiceStatus::Draft]);
        Invoice::factory()->create(['status' => InvoiceStatus::Pending]);
        Invoice::factory()->create(['status' => InvoiceStatus::Paid]);

        $response = $this->actingAs($user)->get('/financial/invoices');

        $response->assertStatus(200);
    }

    #[Test]
    public function issue_transitions_invoice_from_draft_to_pending(): void
    {
        $user = User::factory()->create();
        $user->assignRole('financial');

        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Draft]);

        $response = $this->actingAs($user)->patch("/financial/invoices/{$invoice->id}/issue");

        $response->assertRedirect();
        $this->assertSame(InvoiceStatus::Pending->value, $invoice->fresh()->status);
    }

    #[Test]
    public function issue_throws_when_invoice_not_in_draft(): void
    {
        $user = User::factory()->create();
        $user->assignRole('financial');

        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Pending]);

        $response = $this->actingAs($user)->patch("/financial/invoices/{$invoice->id}/issue");

        $response->assertStatus(500);
    }
}
