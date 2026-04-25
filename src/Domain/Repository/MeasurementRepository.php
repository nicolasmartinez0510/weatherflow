<?php

declare(strict_types=1);

namespace WeatherFlow\Domain\Repository;

use WeatherFlow\Domain\Entity\Measurement;
use WeatherFlow\Domain\Entity\WeatherflowEntity;
use WeatherFlow\Domain\ValueObject\Id;
use WeatherFlow\Domain\ValueObject\WeatherStationId;

interface MeasurementRepository
{
    public function save(Measurement $measurement): void;

    public function findById(Id $id): ?WeatherflowEntity;

    /**
     * @return list<Measurement>
     */
    public function findByWeatherStationId(WeatherStationId $weatherStationId): array;

    /**
     * @return list<Measurement>
     */
    public function findHistory(
        ?string $stationName,
        ?float $minTemperature,
        ?float $maxTemperature,
        bool $alertsOnly,
    ): array;

    public function delete(Id $id): void;
}
