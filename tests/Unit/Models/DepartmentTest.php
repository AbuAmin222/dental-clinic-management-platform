<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Department;
use App\Models\Receptionist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function casts_is_active_as_boolean(): void
    {
        $department = Department::factory()->create(['is_active' => true]);

        $this->assertTrue($department->is_active);
    }

    #[Test]
    public function scope_active_filters_only_active_departments(): void
    {
        Department::factory()->create(['is_active' => true]);
        Department::factory()->create(['is_active' => false]);
        Department::factory()->create(['is_active' => true]);

        $active = Department::active()->get();

        $this->assertCount(2, $active);
    }

    #[Test]
    public function receptionists_relationship(): void
    {
        $department = Department::factory()->create();
        $user = \App\Models\User::factory()->create();
        Receptionist::factory()->create(['department_id' => $department->id]);

        $this->assertCount(1, $department->receptionists);
    }

    #[Test]
    public function fillable_attributes(): void
    {
        $fillable = (new Department())->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('slug', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('is_active', $fillable);
    }
}
