<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Doctor;
use App\Models\Specialization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SpecializationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function casts_is_active_as_boolean(): void
    {
        $specialization = Specialization::factory()->create(['is_active' => true]);

        $this->assertTrue($specialization->is_active);
    }

    #[Test]
    public function scope_active_filters_only_active_specializations(): void
    {
        Specialization::factory()->create(['is_active' => true]);
        Specialization::factory()->create(['is_active' => false]);
        Specialization::factory()->create(['is_active' => true]);

        $active = Specialization::active()->get();

        $this->assertCount(2, $active);
    }

    #[Test]
    public function doctors_relationship(): void
    {
        $specialization = Specialization::factory()->create();
        Doctor::factory()->create(['specialization_id' => $specialization->id]);

        $this->assertCount(1, $specialization->doctors);
    }

    #[Test]
    public function fillable_attributes(): void
    {
        $fillable = (new Specialization())->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('slug', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('is_active', $fillable);
    }
}
