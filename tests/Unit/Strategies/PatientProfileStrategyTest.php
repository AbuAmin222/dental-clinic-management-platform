<?php

declare(strict_types=1);

namespace Tests\Unit\Strategies;

use App\Models\Patient;
use App\Enums\BloodGroup;
use App\Models\User;
use App\Strategies\Profile\PatientProfileStrategy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PatientProfileStrategyTest extends TestCase
{
    use RefreshDatabase;

    private PatientProfileStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new PatientProfileStrategy();
    }

    #[Test]
    public function create_stores_patient_profile(): void
    {
        $user = User::factory()->create();

        $this->strategy->create($user, [
            'role' => 'patient',
            'blood_group' => 'O+',
            'allergies' => 'Penicillin',
            'chronic_diseases' => 'Diabetes',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_phone' => '0591234567',
            'medical_notes' => 'Routine checkup',
        ]);

        $patient = Patient::where('user_id', $user->id)->first();

        $this->assertNotNull($patient);
        $this->assertSame(BloodGroup::from('O+'), $patient->blood_group);
        $this->assertSame('Penicillin', $patient->allergies);
        $this->assertSame('Jane Doe', $patient->emergency_contact_name);
    }

    #[Test]
    public function update_modifies_patient_profile(): void
    {
        $user = User::factory()->create();
        Patient::factory()->create([
            'user_id' => $user->id,
            'allergies' => 'Old allergies',
        ]);

        $this->strategy->update($user, [
            'blood_group' => 'AB+',
            'allergies' => 'Updated allergies',
        ]);

        $patient = Patient::where('user_id', $user->id)->first();

        $this->assertSame(BloodGroup::ABPositive, $patient->blood_group);
        $this->assertSame('Updated allergies', $patient->allergies);
    }

    #[Test]
    public function delete_removes_patient_profile(): void
    {
        $user = User::factory()->create();
        Patient::factory()->create(['user_id' => $user->id]);

        $this->strategy->delete($user);

        $this->assertDatabaseMissing('patients', ['user_id' => $user->id]);
    }
}
