<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WeatherFlow\Domain\ValueObject\Humidity;

final class HumidityTest extends TestCase
{
    public function test_accepts_zero_and_100(): void
    {
        $this->assertSame(0.0, (new Humidity(0.0))->percent);
        $this->assertSame(100.0, (new Humidity(100.0))->percent);
    }

    public function test_rejects_below_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Humidity(-0.01);
    }

    public function test_rejects_above_100(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Humidity(100.01);
    }
}
