<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\Gender;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GenderTest extends TestCase
{
    #[Test]
    public function values_method_returns_all_genders(): void
    {
        $values = Gender::values();

        $this->assertSame(['Male', 'Female'], $values);
    }

    #[Test]
    public function cases_are_string_backed(): void
    {
        $this->assertSame('Male', Gender::Male->value);
        $this->assertSame('Female', Gender::Female->value);
    }

    #[Test]
    public function all_cases_can_be_created_from_value(): void
    {
        foreach (Gender::cases() as $gender) {
            $this->assertSame($gender, Gender::from($gender->value));
        }
    }
}
