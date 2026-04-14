<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\Measurement;

use WeatherFlow\Application\Exception\StationNotFoundException;
use WeatherFlow\Domain\Repository\MeasurementRepository;
use WeatherFlow\Domain\Repository\WeatherStationRepository;
use WeatherFlow\Domain\ValueObject\StationId;

final readonly class ListMeasurementsByStationUseCase
{
    public function __construct(
        private WeatherStationRepository $stations,
        private MeasurementRepository $measurements,
    ) {}

    /**
     * @return list<MeasurementResponse>
     */
    public function execute(string $stationId): array
    {
        if ($this->stations->findById(new StationId($stationId)) === null) {
            throw new StationNotFoundException();
        }

        $items = $this->measurements->findByStationId(new StationId($stationId));

        return array_map(
            static fn ($m) => MeasurementResponse::fromEntity($m),
            $items,
        );
    }
}
