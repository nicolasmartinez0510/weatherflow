<?php

declare(strict_types=1);

namespace Tests\Support;

use WeatherFlow\Domain\Entity\WeatherStation;
use WeatherFlow\Domain\Repository\WeatherStationRepository;
use WeatherFlow\Domain\ValueObject\Id;
use WeatherFlow\Domain\ValueObject\StationId;

final class InMemoryWeatherStationRepository implements WeatherStationRepository
{
    /** @var array<string, WeatherStation> */
    private array $stations = [];

    public function save(WeatherStation $station): void
    {
        $this->stations[$station->id()->value] = $station;
    }

    public function findById(Id $id): ?WeatherStation
    {
        return $this->stations[$id->value] ?? null;
    }

    public function delete(Id $id): void
    {
        unset($this->stations[$id->value]);
    }
}
