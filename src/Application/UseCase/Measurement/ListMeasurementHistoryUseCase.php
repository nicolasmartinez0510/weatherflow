<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\Measurement;

use WeatherFlow\Domain\Repository\MeasurementRepository;

final readonly class ListMeasurementHistoryUseCase
{
    public function __construct(private MeasurementRepository $measurements) {}

    /**
     * @return list<MeasurementResponse>
     */
    public function execute(
        ?string $stationName,
        ?float $minTemperature,
        ?float $maxTemperature,
        bool $alertsOnly,
    ): array {
        $items = $this->measurements->findHistory(
            $stationName,
            $minTemperature,
            $maxTemperature,
            $alertsOnly,
        );

        return array_map(
            static fn ($m) => MeasurementResponse::fromEntity($m),
            $items,
        );
    }
}
