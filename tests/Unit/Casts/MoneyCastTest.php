<?php

declare(strict_types=1);

namespace Tests\Unit\Casts;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MoneyCastTest extends TestCase
{
    private MoneyCast $cast;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cast = new MoneyCast();
    }

    #[Test]
    public function get_converts_minor_units_to_major_float(): void
    {
        $model = $this->createMock(Model::class);
        $value = 12500;

        $result = $this->cast->get($model, 'amount', $value, []);

        $this->assertSame(125.00, $result);
    }

    #[Test]
    public function get_handles_zero_value(): void
    {
        $model = $this->createMock(Model::class);
        $result = $this->cast->get($model, 'amount', 0, []);

        $this->assertSame(0.0, $result);
    }

    #[Test]
    public function get_returns_null_for_null_input(): void
    {
        $model = $this->createMock(Model::class);

        $result = $this->cast->get($model, 'amount', null, []);

        $this->assertNull($result);
    }

    #[Test]
    public function get_handles_small_values(): void
    {
        $model = $this->createMock(Model::class);

        $result = $this->cast->get($model, 'amount', 99, []);

        $this->assertSame(0.99, $result);
    }

    #[Test]
    public function get_handles_large_values(): void
    {
        $model = $this->createMock(Model::class);

        $result = $this->cast->get($model, 'amount', 999999999, []);

        $this->assertSame(9999999.99, $result);
    }

    #[Test]
    public function set_converts_major_float_to_minor_units(): void
    {
        $model = $this->createMock(Model::class);

        $result = $this->cast->set($model, 'amount', 125.00, []);

        $this->assertSame(12500, $result);
    }

    #[Test]
    public function set_handles_decimal_fractions(): void
    {
        $model = $this->createMock(Model::class);

        $result = $this->cast->set($model, 'amount', 99.99, []);

        $this->assertSame(9999, $result);
    }

    #[Test]
    public function set_returns_null_for_null_input(): void
    {
        $model = $this->createMock(Model::class);

        $result = $this->cast->set($model, 'amount', null, []);

        $this->assertNull($result);
    }

    #[Test]
    public function set_handles_zero_value(): void
    {
        $model = $this->createMock(Model::class);

        $result = $this->cast->set($model, 'amount', 0.0, []);

        $this->assertSame(0, $result);
    }

    #[Test]
    public function round_trip_preserves_value(): void
    {
        $model = $this->createMock(Model::class);
        $original = 123.45;

        $stored = $this->cast->set($model, 'amount', $original, []);
        $retrieved = $this->cast->get($model, 'amount', $stored, []);

        $this->assertSame($original, $retrieved);
    }
}
