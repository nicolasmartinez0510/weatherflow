<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\Measurement;

use WeatherFlow\Application\Exception\StationNotFoundException;
use WeatherFlow\Domain\Repository\MeasurementRepository;
use WeatherFlow\Domain\Repository\WeatherStationRepository;
use WeatherFlow\Domain\ValueObject\WeatherStationId;

final readonly class ListMeasurementsByWeatherStationUseCase
{
    public function __construct(
        private WeatherStationRepository $weatherStations,
        private MeasurementRepository $measurements,
    ) {}

    /**
     * @return list<MeasurementResponse>
     */
    public function execute(string $weatherStationId): array
    {
        if ($this->weatherStations->findById(new WeatherStationId($weatherStationId)) === null) {
            throw new StationNotFoundException();
        }

        $items = $this->measurements->findByWeatherStationId(new WeatherStationId($weatherStationId));

        return array_map(
            static fn ($m) => MeasurementResponse::fromEntity($m),
            $items,
        );
    }
}
