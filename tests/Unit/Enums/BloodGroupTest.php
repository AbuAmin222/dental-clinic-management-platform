<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\BloodGroup;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BloodGroupTest extends TestCase
{
    #[Test]
    public function values_method_returns_all_blood_groups(): void
    {
        $values = BloodGroup::values();

        $this->assertSame(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], $values);
    }

    #[Test]
    public function cases_are_string_backed(): void
    {
        $this->assertSame('A+', BloodGroup::APositive->value);
        $this->assertSame('A-', BloodGroup::ANegative->value);
        $this->assertSame('B+', BloodGroup::BPositive->value);
        $this->assertSame('B-', BloodGroup::BNegative->value);
        $this->assertSame('AB+', BloodGroup::ABPositive->value);
        $this->assertSame('AB-', BloodGroup::ABNegative->value);
        $this->assertSame('O+', BloodGroup::OPositive->value);
        $this->assertSame('O-', BloodGroup::ONegative->value);
    }

    #[Test]
    public function all_cases_can_be_created_from_value(): void
    {
        foreach (BloodGroup::cases() as $group) {
            $this->assertSame($group, BloodGroup::from($group->value));
        }
    }
}
