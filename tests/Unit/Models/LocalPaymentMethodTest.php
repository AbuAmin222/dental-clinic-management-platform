<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Financial;
use App\Models\LocalPaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LocalPaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function casts_is_visible_to_patient_as_boolean(): void
    {
        $method = LocalPaymentMethod::factory()->create(['is_visible_to_patient' => true]);

        $this->assertTrue($method->is_visible_to_patient);
    }

    #[Test]
    public function casts_is_active_as_boolean(): void
    {
        $method = LocalPaymentMethod::factory()->create(['is_active' => true]);

        $this->assertTrue($method->is_active);
    }

    #[Test]
    public function casts_visa_card_number_as_encrypted(): void
    {
        $method = LocalPaymentMethod::factory()->create(['visa_card_number' => '4111111111111111']);

        $this->assertNotSame('4111111111111111', $method->getRawOriginal('visa_card_number'));
        $this->assertSame('4111111111111111', $method->visa_card_number);
    }

    #[Test]
    public function hidden_visa_card_number_attribute(): void
    {
        $method = LocalPaymentMethod::factory()->create(['visa_card_number' => '4111111111111111']);

        $array = $method->toArray();

        $this->assertArrayNotHasKey('visa_card_number', $array);
    }

    #[Test]
    public function masked_visa_number_returns_last_four_digits(): void
    {
        $method = LocalPaymentMethod::factory()->create(['visa_card_number' => '4111111111111111']);

        $this->assertSame('**** **** **** 1111', $method->masked_visa_number);
    }

    #[Test]
    public function masked_visa_number_returns_null_when_no_card(): void
    {
        $method = LocalPaymentMethod::factory()->create(['visa_card_number' => null]);

        $this->assertNull($method->masked_visa_number);
    }

    #[Test]
    public function masked_visa_number_strips_non_digits(): void
    {
        $method = LocalPaymentMethod::factory()->create(['visa_card_number' => '4111-1111-1111-1111']);

        $this->assertSame('**** **** **** 1111', $method->masked_visa_number);
    }

    #[Test]
    public function scope_for_patient_display_filters_active_and_visible(): void
    {
        $financial = Financial::factory()->create();
        LocalPaymentMethod::factory()->create([
            'financial_id' => $financial->id,
            'is_active' => true,
            'is_visible_to_patient' => true,
        ]);
        LocalPaymentMethod::factory()->create([
            'financial_id' => $financial->id,
            'is_active' => false,
            'is_visible_to_patient' => true,
        ]);
        LocalPaymentMethod::factory()->create([
            'financial_id' => $financial->id,
            'is_active' => true,
            'is_visible_to_patient' => false,
        ]);

        $visible = LocalPaymentMethod::forPatientDisplay()->get();

        $this->assertCount(1, $visible);
    }

    #[Test]
    public function scope_active_filters_inactive_methods(): void
    {
        $financial = Financial::factory()->create();
        LocalPaymentMethod::factory()->create(['financial_id' => $financial->id, 'is_active' => true]);
        LocalPaymentMethod::factory()->create(['financial_id' => $financial->id, 'is_active' => false]);

        $active = LocalPaymentMethod::active()->get();

        $this->assertCount(1, $active);
    }

    #[Test]
    public function financial_relationship(): void
    {
        $financial = Financial::factory()->create();
        $method = LocalPaymentMethod::factory()->create(['financial_id' => $financial->id]);

        $this->assertInstanceOf(Financial::class, $method->financial);
    }
}
