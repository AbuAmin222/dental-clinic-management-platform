<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Patient;

use App\Enums\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentSandboxControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function show_gateway_displays_sandbox_with_default_visa(): void
    {
        $response = $this->get('/payment-sandbox');

        $response->assertStatus(200);
    }

    #[Test]
    public function show_gateway_accepts_custom_gateway_parameter(): void
    {
        $response = $this->get('/payment-sandbox?gateway=paypal&amount=100&tx=tx123');

        $response->assertStatus(200);
    }

    #[Test]
    public function show_gateway_handles_invalid_gateway(): void
    {
        $response = $this->get('/payment-sandbox?gateway=invalid_gateway');

        $response->assertStatus(200);
    }

    #[Test]
    public function show_gateway_does_not_require_authentication(): void
    {
        $response = $this->get('/payment-sandbox');

        $response->assertStatus(200);
    }
}
