<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WeatherFlow\Domain\Entity\WeatherStation;
use WeatherFlow\Domain\ValueObject\Coordinates;
use WeatherFlow\Domain\ValueObject\WeatherStationId;
use WeatherFlow\Domain\ValueObject\WeatherStationStatus;
use WeatherFlow\Domain\ValueObject\UserId;

final class WeatherStationEntityTest extends TestCase
{
    public function test_empty_name_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Station name cannot be empty.');

        new WeatherStation(
            new WeatherStationId('st-1'),
            '',
            new Coordinates(-34.0, -58.0),
            'DHT22',
            WeatherStationStatus::Active,
            new UserId('user-1'),
        );
    }

    public function test_empty_sensor_model_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Sensor model cannot be empty.');

        new WeatherStation(
            new WeatherStationId('st-1'),
            'Central',
            new Coordinates(-34.0, -58.0),
            '',
            WeatherStationStatus::Active,
            new UserId('user-1'),
        );
    }

    public function test_relocate_updates_coordinates(): void
    {
        $station = new WeatherStation(
            new WeatherStationId('st-1'),
            'Central',
            new Coordinates(-34.0, -58.0),
            'DHT22',
            WeatherStationStatus::Active,
            new UserId('user-1'),
        );

        $station->relocate(new Coordinates(0.0, 0.0));

        $this->assertSame(0.0, $station->coordinates()->latitude);
        $this->assertSame(0.0, $station->coordinates()->longitude);
    }

    public function test_set_status(): void
    {
        $station = new WeatherStation(
            new WeatherStationId('st-1'),
            'Central',
            new Coordinates(-34.0, -58.0),
            'DHT22',
            WeatherStationStatus::Active,
            new UserId('user-1'),
        );

        $station->setStatus(WeatherStationStatus::Inactive);

        $this->assertSame(WeatherStationStatus::Inactive, $station->status());
    }

    public function test_rename_empty_string_throws(): void
    {
        $station = new WeatherStation(
            new WeatherStationId('st-1'),
            'Central',
            new Coordinates(-34.0, -58.0),
            'DHT22',
            WeatherStationStatus::Active,
            new UserId('user-1'),
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Station name cannot be empty.');
        $station->rename('');
    }

    public function test_change_sensor_model_empty_string_throws(): void
    {
        $station = new WeatherStation(
            new WeatherStationId('st-1'),
            'Central',
            new Coordinates(-34.0, -58.0),
            'DHT22',
            WeatherStationStatus::Active,
            new UserId('user-1'),
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Sensor model cannot be empty.');
        $station->changeSensorModel('');
    }
}
