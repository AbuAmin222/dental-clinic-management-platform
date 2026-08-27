<?php

declare(strict_types=1);

namespace Tests\Unit\Strategies;

use App\Models\Doctor;
use App\Models\Specialization;
use App\Models\User;
use App\Strategies\Profile\DoctorProfileStrategy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DoctorProfileStrategyTest extends TestCase
{
    use RefreshDatabase;

    private DoctorProfileStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new DoctorProfileStrategy();
    }

    #[Test]
    public function create_stores_doctor_profile(): void
    {
        $user = User::factory()->create();
        $specialization = Specialization::factory()->create();

        $this->strategy->create($user, [
            'role' => 'doctor',
            'specialization_id' => $specialization->id,
            'license_number' => 'DOC-12345',
            'experience_years' => 10,
            'bio' => 'Expert dentist',
        ]);

        $doctor = Doctor::where('user_id', $user->id)->first();

        $this->assertNotNull($doctor);
        $this->assertSame($specialization->id, $doctor->specialization_id);
        $this->assertSame('DOC-12345', $doctor->license_number);
        $this->assertSame(10, $doctor->experience_years);
        $this->assertSame('Expert dentist', $doctor->bio);
    }

    #[Test]
    public function create_throws_when_specialization_not_found(): void
    {
        $user = User::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        $this->strategy->create($user, [
            'role' => 'doctor',
            'specialization_id' => 999,
            'license_number' => 'DOC-12345',
        ]);
    }

    #[Test]
    public function update_modifies_doctor_profile(): void
    {
        $user = User::factory()->create();
        $specialization = Specialization::factory()->create();
        Doctor::factory()->create([
            'user_id' => $user->id,
            'license_number' => 'DOC-OLD',
            'experience_years' => 5,
        ]);

        $this->strategy->update($user, [
            'specialization_id' => $specialization->id,
            'license_number' => 'DOC-NEW',
            'experience_years' => 8,
            'bio' => 'Updated bio',
        ]);

        $doctor = Doctor::where('user_id', $user->id)->first();

        $this->assertSame('DOC-NEW', $doctor->license_number);
        $this->assertSame(8, $doctor->experience_years);
        $this->assertSame('Updated bio', $doctor->bio);
    }

    #[Test]
    public function update_throws_when_doctor_profile_not_found(): void
    {
        $user = User::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        $this->strategy->update($user, ['license_number' => 'DOC-NEW']);
    }

    #[Test]
    public function delete_removes_doctor_profile(): void
    {
        $user = User::factory()->create();
        Doctor::factory()->create(['user_id' => $user->id]);

        $this->strategy->delete($user);

        $this->assertDatabaseMissing('doctors', ['user_id' => $user->id]);
    }
}
