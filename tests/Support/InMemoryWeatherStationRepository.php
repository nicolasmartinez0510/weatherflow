<?php

declare(strict_types=1);

namespace Tests\Support;

use WeatherFlow\Domain\Entity\WeatherStation;
use WeatherFlow\Domain\Repository\WeatherStationRepository;
use WeatherFlow\Domain\ValueObject\Id;
use WeatherFlow\Domain\ValueObject\WeatherStationId;

final class InMemoryWeatherStationRepository implements WeatherStationRepository
{
    /** @var array<string, WeatherStation> */
    private array $weatherStations = [];

    public function save(WeatherStation $weatherStation): void
    {
        $this->weatherStations[$weatherStation->id()->value] = $weatherStation;
    }

    public function findById(Id $id): ?WeatherStation
    {
        return $this->weatherStations[$id->value] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->weatherStations);
    }

    public function delete(Id $id): void
    {
        unset($this->weatherStations[$id->value]);
    }
}
