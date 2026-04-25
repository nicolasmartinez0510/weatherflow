<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\WeatherStation;

use WeatherFlow\Domain\Repository\WeatherStationRepository;
use WeatherFlow\Domain\ValueObject\WeatherStationId;

final readonly class DeleteWeatherStationUseCase
{
    public function __construct(
        private WeatherStationRepository $weatherStations,
    ) {}

    public function execute(string $id): void
    {
        $this->weatherStations->delete(new WeatherStationId($id));
    }
}
