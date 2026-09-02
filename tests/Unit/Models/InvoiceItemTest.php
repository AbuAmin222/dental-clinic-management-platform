<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Casts\MoneyCast;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Pricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvoiceItemTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function casts_unit_price_and_total_price_with_money_cast(): void
    {
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Test Service',
            'quantity' => 2,
            'unit_price' => 50.0,
        ]);

        $this->assertSame(50.0, $item->unit_price);
        $this->assertSame(100.0, $item->total_price);
    }

    #[Test]
    public function saving_event_calculates_total_price(): void
    {
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Test Service',
            'quantity' => 3,
            'unit_price' => 25.0,
        ]);

        $invoice->recalculateTotals();

        $invoice = $invoice->fresh();
        $this->assertSame(7500, (int) $invoice->getRawOriginal('sub_total'));
    }

    #[Test]
    public function total_price_updates_on_save_when_quantity_changes(): void
    {
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Test',
            'quantity' => 2,
            'unit_price' => 10.0,
        ]);

        $this->assertSame(2000, (int) $item->getRawOriginal('total_price'));

        $item->update(['quantity' => 5]);

        $this->assertSame(5000, (int) $item->getRawOriginal('total_price'));
    }

    #[Test]
    public function invoice_relationship(): void
    {
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_name' => 'Test',
            'quantity' => 1,
            'unit_price' => 10.0,
        ]);

        $this->assertInstanceOf(Invoice::class, $item->invoice);
    }

    #[Test]
    public function pricing_relationship(): void
    {
        $pricing = Pricing::factory()->create();
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'pricing_id' => $pricing->id,
            'item_name' => 'Test',
            'quantity' => 1,
            'unit_price' => 10.0,
        ]);

        $this->assertInstanceOf(Pricing::class, $item->pricing);
    }
}
