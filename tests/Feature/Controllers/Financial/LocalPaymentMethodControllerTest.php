<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Financial;

use App\Models\Financial;
use App\Models\LocalPaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LocalPaymentMethodControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    #[Test]
    public function index_displays_payment_methods_for_financial(): void
    {
        $user = User::factory()->create();
        $user->assignRole('financial');
        $financial = Financial::factory()->create(['user_id' => $user->id]);

        LocalPaymentMethod::factory()->create(['financial_id' => $financial->id]);

        $response = $this->actingAs($user)->get('/financial/payment-methods');

        $response->assertStatus(200);
    }

    #[Test]
    public function index_returns_403_for_non_financial(): void
    {
        $user = User::factory()->create();
        $user->assignRole('doctor');

        $response = $this->actingAs($user)->get('/financial/payment-methods');

        $response->assertStatus(403);
    }

    #[Test]
    public function store_creates_new_payment_method(): void
    {
        $user = User::factory()->create();
        $user->assignRole('financial');
        $financial = Financial::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post('/financial/payment-methods', [
            'title' => 'Test Payment Method',
            'is_visible_to_patient' => true,
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('local_payment_methods', [
            'financial_id' => $financial->id,
            'title' => 'Test Payment Method',
        ]);
    }

    #[Test]
    public function store_requires_title(): void
    {
        $user = User::factory()->create();
        $user->assignRole('financial');
        Financial::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post('/financial/payment-methods', [
            'is_visible_to_patient' => true,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('title');
    }

    #[Test]
    public function update_modifies_payment_method(): void
    {
        $user = User::factory()->create();
        $user->assignRole('financial');
        $financial = Financial::factory()->create(['user_id' => $user->id]);
        $method = LocalPaymentMethod::factory()->create(['financial_id' => $financial->id]);

        $response = $this->actingAs($user)->put("/financial/payment-methods/{$method->id}", [
            'title' => 'Updated Method',
            'is_visible_to_patient' => false,
            'is_active' => false,
        ]);

        $response->assertRedirect();
        $this->assertSame('Updated Method', $method->fresh()->title);
    }

    #[Test]
    public function update_returns_403_for_other_financial(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('financial');
        $ownerFinancial = Financial::factory()->create(['user_id' => $owner->id]);
        $method = LocalPaymentMethod::factory()->create(['financial_id' => $ownerFinancial->id]);

        $other = User::factory()->create();
        $other->assignRole('financial');
        Financial::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($other)->put("/financial/payment-methods/{$method->id}", [
            'title' => 'Hacked',
            'is_visible_to_patient' => false,
            'is_active' => false,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function destroy_deletes_payment_method(): void
    {
        $user = User::factory()->create();
        $user->assignRole('financial');
        $financial = Financial::factory()->create(['user_id' => $user->id]);
        $method = LocalPaymentMethod::factory()->create(['financial_id' => $financial->id]);

        $response = $this->actingAs($user)->delete("/financial/payment-methods/{$method->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted($method);
    }
}
