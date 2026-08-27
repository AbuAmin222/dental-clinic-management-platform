<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\AdminAccessLevel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AdminAccessLevelTest extends TestCase
{
    #[Test]
    public function values_method_returns_all_levels(): void
    {
        $values = AdminAccessLevel::values();

        $this->assertSame(['super_admin', 'admin'], $values);
    }

    #[Test]
    public function cases_are_string_backed(): void
    {
        $this->assertSame('super_admin', AdminAccessLevel::SuperAdmin->value);
        $this->assertSame('admin', AdminAccessLevel::Admin->value);
    }

    #[Test]
    public function all_cases_can_be_created_from_value(): void
    {
        foreach (AdminAccessLevel::cases() as $level) {
            $this->assertSame($level, AdminAccessLevel::from($level->value));
        }
    }

    #[Test]
    public function label_returns_correct_display_name(): void
    {
        $this->assertSame('Root manager (full powers protected)', AdminAccessLevel::SuperAdmin->label());
        $this->assertSame('System administrator', AdminAccessLevel::Admin->label());
    }
}
