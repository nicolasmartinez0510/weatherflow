<?php

declare(strict_types=1);

namespace Tests\Support;

use WeatherFlow\Domain\Entity\WeatherStation;
use WeatherFlow\Domain\Repository\WeatherStationRepository;
use WeatherFlow\Domain\ValueObject\StationId;

final class InMemoryWeatherStationRepository implements WeatherStationRepository
{
    /** @var array<string, WeatherStation> */
    private array $stations = [];

    public function save(WeatherStation $station): void
    {
        $this->stations[$station->id()->value] = $station;
    }

    public function findById(StationId $id): ?WeatherStation
    {
        return $this->stations[$id->value] ?? null;
    }

    public function delete(StationId $id): void
    {
        unset($this->stations[$id->value]);
    }
}
