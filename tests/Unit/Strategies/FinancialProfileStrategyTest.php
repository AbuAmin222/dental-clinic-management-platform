<?php

declare(strict_types=1);

namespace Tests\Unit\Strategies;

use App\Models\Financial;
use App\Models\User;
use App\Strategies\Profile\FinancialProfileStrategy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FinancialProfileStrategyTest extends TestCase
{
    use RefreshDatabase;

    private FinancialProfileStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new FinancialProfileStrategy();
    }

    #[Test]
    public function create_stores_financial_profile(): void
    {
        $user = User::factory()->create();

        $this->strategy->create($user, [
            'role' => 'financial',
            'employee_number' => 'FIN-123',
            'hiring_date' => '2023-01-15',
            'years_experience' => 5,
            'specialization' => 'Accounts Receivable',
        ]);

        $financial = Financial::where('user_id', $user->id)->first();

        $this->assertNotNull($financial);
        $this->assertSame('FIN-123', $financial->employee_number);
        $this->assertSame(5, $financial->years_experience);
        $this->assertSame('Accounts Receivable', $financial->specialization);
    }

    #[Test]
    public function create_uses_defaults_for_optional_fields(): void
    {
        $user = User::factory()->create();

        $this->strategy->create($user, [
            'employee_number' => 'FIN-456',
        ]);

        $financial = Financial::where('user_id', $user->id)->first();

        $this->assertSame(0, $financial->years_experience);
        $this->assertNull($financial->hiring_date);
    }

    #[Test]
    public function update_modifies_financial_profile(): void
    {
        $user = User::factory()->create();
        $financial = Financial::factory()->create(['user_id' => $user->id]);

        $this->strategy->update($user, [
            'employee_number' => 'FIN-UPDATED',
            'years_experience' => 10,
        ]);

        $financial->refresh();

        $this->assertSame('FIN-UPDATED', $financial->employee_number);
        $this->assertSame(10, $financial->years_experience);
    }

    #[Test]
    public function delete_removes_financial_profile(): void
    {
        $user = User::factory()->create();
        Financial::factory()->create(['user_id' => $user->id]);

        $this->strategy->delete($user);

        $this->assertDatabaseMissing('financials', ['user_id' => $user->id]);
    }
}
