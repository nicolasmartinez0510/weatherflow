<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\WeatherStation;

use WeatherFlow\Domain\Repository\WeatherStationRepository;

final readonly class ListWeatherStationsUseCase
{
    public function __construct(
        private WeatherStationRepository $weatherStations,
    ) {}

    /**
     * @return list<WeatherStationResponse>
     */
    public function execute(): array
    {
        return array_map(
            static fn ($station) => WeatherStationResponse::fromEntity($station),
            $this->weatherStations->findAll(),
        );
    }
}
