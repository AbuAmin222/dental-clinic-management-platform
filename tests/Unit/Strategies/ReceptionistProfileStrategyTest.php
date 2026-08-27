<?php

declare(strict_types=1);

namespace Tests\Unit\Strategies;

use App\Models\Department;
use App\Models\Receptionist;
use App\Models\User;
use App\Strategies\Profile\ReceptionistProfileStrategy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReceptionistProfileStrategyTest extends TestCase
{
    use RefreshDatabase;

    private ReceptionistProfileStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new ReceptionistProfileStrategy();
    }

    #[Test]
    public function create_stores_receptionist_profile(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->create();

        $this->strategy->create($user, [
            'role' => 'receptionist',
            'department_id' => $department->id,
            'employee_number' => 'EMP-123',
            'hiring_date' => '2023-01-15',
        ]);

        $receptionist = Receptionist::where('user_id', $user->id)->first();

        $this->assertNotNull($receptionist);
        $this->assertSame($department->id, $receptionist->department_id);
        $this->assertSame('EMP-123', $receptionist->employee_number);
        $this->assertSame('2023-01-15', $receptionist->hiring_date->format('Y-m-d'));
    }

    #[Test]
    public function update_modifies_receptionist_profile(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->create();
        Receptionist::factory()->create([
            'user_id' => $user->id,
            'employee_number' => 'EMP-OLD',
        ]);

        $this->strategy->update($user, [
            'department_id' => $department->id,
            'employee_number' => 'EMP-NEW',
            'hiring_date' => '2023-06-01',
        ]);

        $receptionist = Receptionist::where('user_id', $user->id)->first();

        $this->assertSame('EMP-NEW', $receptionist->employee_number);
        $this->assertSame($department->id, $receptionist->department_id);
    }

    #[Test]
    public function delete_removes_receptionist_profile(): void
    {
        $user = User::factory()->create();
        Receptionist::factory()->create(['user_id' => $user->id]);

        $this->strategy->delete($user);

        $this->assertDatabaseMissing('receptionists', ['user_id' => $user->id]);
    }
}
