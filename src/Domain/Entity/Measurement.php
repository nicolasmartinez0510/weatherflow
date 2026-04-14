<?php

declare(strict_types=1);

namespace WeatherFlow\Domain\Entity;

use DateTimeImmutable;
use InvalidArgumentException;
use WeatherFlow\Domain\ValueObject\Humidity;
use WeatherFlow\Domain\ValueObject\MeasurementId;
use WeatherFlow\Domain\ValueObject\StationId;

final class Measurement
{
    public function __construct(
        private readonly MeasurementId $id,
        private readonly StationId $stationId,
        private float $temperatureCelsius,
        private Humidity $humidity,
        private float $pressureHpa,
        private DateTimeImmutable $reportedAt,
        private bool $alert,
        private string $alertType,
    ) {
        if ($pressureHpa <= 0.0) {
            throw new InvalidArgumentException('Atmospheric pressure must be positive.');
        }
    }

    public function id(): MeasurementId
    {
        return $this->id;
    }

    public function stationId(): StationId
    {
        return $this->stationId;
    }

    public function temperatureCelsius(): float
    {
        return $this->temperatureCelsius;
    }

    public function humidity(): Humidity
    {
        return $this->humidity;
    }

    public function pressureHpa(): float
    {
        return $this->pressureHpa;
    }

    public function reportedAt(): DateTimeImmutable
    {
        return $this->reportedAt;
    }

    public function alert(): bool
    {
        return $this->alert;
    }

    public function alertType(): string
    {
        return $this->alertType;
    }

    public function applyClimateState(
        float $temperatureCelsius,
        Humidity $humidity,
        float $pressureHpa,
        DateTimeImmutable $reportedAt,
        bool $alert,
        string $alertType,
    ): void {
        if ($pressureHpa <= 0.0) {
            throw new InvalidArgumentException('Atmospheric pressure must be positive.');
        }
        $this->temperatureCelsius = $temperatureCelsius;
        $this->humidity = $humidity;
        $this->pressureHpa = $pressureHpa;
        $this->reportedAt = $reportedAt;
        $this->alert = $alert;
        $this->alertType = $alertType;
    }
}
