<?php

declare(strict_types=1);

namespace WeatherFlow\Domain\Repository;

use WeatherFlow\Domain\Entity\WeatherflowEntity;
use WeatherFlow\Domain\Entity\WeatherStation;
use WeatherFlow\Domain\ValueObject\Id;

interface WeatherStationRepository
{
    public function save(WeatherStation $weatherStation): void;

    public function findById(Id $id): ?WeatherflowEntity;

    /**
     * @return list<WeatherStation>
     */
    public function findAll(): array;

    public function delete(Id $id): void;
}
