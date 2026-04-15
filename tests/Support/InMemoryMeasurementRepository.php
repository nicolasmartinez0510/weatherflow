<?php

declare(strict_types=1);

namespace Tests\Support;

use WeatherFlow\Domain\Entity\Measurement;
use WeatherFlow\Domain\Repository\MeasurementRepository;
use WeatherFlow\Domain\Repository\WeatherStationRepository;
use WeatherFlow\Domain\ValueObject\Id;
use WeatherFlow\Domain\ValueObject\MeasurementId;
use WeatherFlow\Domain\ValueObject\StationId;

final class InMemoryMeasurementRepository implements MeasurementRepository
{
    /** @var array<string, Measurement> */
    private array $measurements = [];

    public function __construct(private readonly ?WeatherStationRepository $stations = null) {}

    public function save(Measurement $measurement): void
    {
        $this->measurements[$measurement->id()->value] = $measurement;
    }

    public function findById(Id $id): ?Measurement
    {
        return $this->measurements[$id->value] ?? null;
    }

    public function findByStationId(StationId $stationId): array
    {
        $list = array_values(array_filter(
            $this->measurements,
            static fn (Measurement $m) => $m->stationId()->value === $stationId->value,
        ));

        usort(
            $list,
            static fn (Measurement $a, Measurement $b) => $b->reportedAt() <=> $a->reportedAt(),
        );

        return $list;
    }

    public function findHistory(
        ?string $stationName,
        ?float $minTemperature,
        ?float $maxTemperature,
        bool $alertsOnly,
    ): array {
        $stationNameNeedle = $stationName !== null ? strtolower(trim($stationName)) : null;

        $list = array_values(array_filter(
            $this->measurements,
            function (Measurement $measurement) use ($stationNameNeedle, $minTemperature, $maxTemperature, $alertsOnly): bool {
                if ($alertsOnly && ! $measurement->alert()) {
                    return false;
                }

                $temperature = $measurement->temperatureCelsius();
                if ($minTemperature !== null && $temperature < $minTemperature) {
                    return false;
                }
                if ($maxTemperature !== null && $temperature > $maxTemperature) {
                    return false;
                }

                if ($stationNameNeedle !== null) {
                    if ($this->stations === null) {
                        return false;
                    }

                    $station = $this->stations->findById($measurement->stationId());
                    if ($station === null) {
                        return false;
                    }

                    if (! str_contains(strtolower($station->name()), $stationNameNeedle)) {
                        return false;
                    }
                }

                return true;
            },
        ));

        usort(
            $list,
            static fn (Measurement $a, Measurement $b) => $b->reportedAt() <=> $a->reportedAt(),
        );

        return $list;
    }

    public function delete(Id $id): void
    {
        unset($this->measurements[$id->value]);
    }
}
