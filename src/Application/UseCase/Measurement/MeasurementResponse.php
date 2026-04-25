<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\Measurement;

use DateTimeInterface;
use WeatherFlow\Domain\Entity\Measurement;

final readonly class MeasurementResponse
{
    public function __construct(
        public string $id,
        public string $weatherStationId,
        public float $temperature,
        public float $humidity,
        public float $pressure,
        public string $reportedAt,
        public bool $alert,
        public string $alertType,
    ) {}

    public static function fromEntity(Measurement $measurement): self
    {
        return new self(
            $measurement->id()->value,
            $measurement->weatherStationId()->value,
            $measurement->temperatureCelsius(),
            $measurement->humidity()->percent,
            $measurement->pressureHpa(),
            $measurement->reportedAt()->format(DateTimeInterface::ATOM),
            $measurement->alert(),
            $measurement->alertType(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'weather_station_id' => $this->weatherStationId,
            'temperature' => $this->temperature,
            'humidity' => $this->humidity,
            'pressure' => $this->pressure,
            'reported_at' => $this->reportedAt,
            'alert' => $this->alert,
            'alert_type' => $this->alertType,
        ];
    }
}
