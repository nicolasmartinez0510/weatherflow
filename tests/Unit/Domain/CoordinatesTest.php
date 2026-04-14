<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WeatherFlow\Domain\ValueObject\Coordinates;

final class CoordinatesTest extends TestCase
{
    public function test_accepts_equator_and_prime_meridian(): void
    {
        $c = new Coordinates(0.0, 0.0);
        $this->assertSame(0.0, $c->latitude);
        $this->assertSame(0.0, $c->longitude);
    }

    public function test_latitude_out_of_range_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Latitude must be between -90 and 90.');
        new Coordinates(91.0, 0.0);
    }

    public function test_longitude_out_of_range_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Longitude must be between -180 and 180.');
        new Coordinates(0.0, 181.0);
    }
}
