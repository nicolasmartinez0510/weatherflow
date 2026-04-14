<?php

declare(strict_types=1);

namespace WeatherFlow\Domain\Repository;

use WeatherFlow\Domain\Entity\Measurement;
use WeatherFlow\Domain\ValueObject\MeasurementId;
use WeatherFlow\Domain\ValueObject\StationId;

interface MeasurementRepository
{
    public function save(Measurement $measurement): void;

    public function findById(MeasurementId $id): ?Measurement;

    /**
     * @return list<Measurement>
     */
    public function findByStationId(StationId $stationId): array;

    public function delete(MeasurementId $id): void;
}
