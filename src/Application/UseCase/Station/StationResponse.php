<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\Station;

use WeatherFlow\Domain\Entity\WeatherStation;

final readonly class StationResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public float $latitude,
        public float $longitude,
        public string $sensorModel,
        public string $status,
        public string $ownerId,
    ) {}

    public static function fromEntity(WeatherStation $station): self
    {
        $coords = $station->coordinates();

        return new self(
            $station->id()->value,
            $station->name(),
            $coords->latitude,
            $coords->longitude,
            $station->sensorModel(),
            $station->status()->value,
            $station->ownerId()->value,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'sensor_model' => $this->sensorModel,
            'status' => $this->status,
            'owner_id' => $this->ownerId,
        ];
    }
}
