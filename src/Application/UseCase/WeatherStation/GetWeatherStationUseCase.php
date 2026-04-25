<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\WeatherStation;

use WeatherFlow\Application\Exception\StationNotFoundException;
use WeatherFlow\Domain\Repository\WeatherStationRepository;
use WeatherFlow\Domain\ValueObject\WeatherStationId;

final readonly class GetWeatherStationUseCase
{
    public function __construct(
        private WeatherStationRepository $weatherStations,
    ) {}

    public function execute(string $id): WeatherStationResponse
    {
        $weatherStation = $this->weatherStations->findById(new WeatherStationId($id));
        if ($weatherStation === null) {
            throw new StationNotFoundException();
        }

        return WeatherStationResponse::fromEntity($weatherStation);
    }
}
