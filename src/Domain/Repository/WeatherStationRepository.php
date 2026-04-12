<?php

declare(strict_types=1);

namespace WeatherFlow\Domain\Repository;

use WeatherFlow\Domain\Entity\WeatherStation;
use WeatherFlow\Domain\ValueObject\StationId;

interface WeatherStationRepository
{
    public function save(WeatherStation $station): void;

    public function findById(StationId $id): ?WeatherStation;

    public function delete(StationId $id): void;
}
