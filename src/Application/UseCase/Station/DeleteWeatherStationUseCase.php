<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\Station;

use WeatherFlow\Domain\Repository\WeatherStationRepository;
use WeatherFlow\Domain\ValueObject\StationId;

final readonly class DeleteWeatherStationUseCase
{
    public function __construct(
        private WeatherStationRepository $stations,
    ) {}

    public function execute(string $id): void
    {
        $this->stations->delete(new StationId($id));
    }
}
