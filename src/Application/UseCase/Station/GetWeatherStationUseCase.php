<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\Station;

use WeatherFlow\Application\Exception\StationNotFoundException;
use WeatherFlow\Domain\Repository\WeatherStationRepository;
use WeatherFlow\Domain\ValueObject\StationId;

final readonly class GetWeatherStationUseCase
{
    public function __construct(
        private WeatherStationRepository $stations,
    ) {}

    public function execute(string $id): StationResponse
    {
        $station = $this->stations->findById(new StationId($id));
        if ($station === null) {
            throw new StationNotFoundException();
        }

        return StationResponse::fromEntity($station);
    }
}
