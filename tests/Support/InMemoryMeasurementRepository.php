<?php

declare(strict_types=1);

namespace Tests\Support;

use WeatherFlow\Domain\Entity\Measurement;
use WeatherFlow\Domain\Repository\MeasurementRepository;
use WeatherFlow\Domain\ValueObject\MeasurementId;
use WeatherFlow\Domain\ValueObject\StationId;

final class InMemoryMeasurementRepository implements MeasurementRepository
{
    /** @var array<string, Measurement> */
    private array $measurements = [];

    public function save(Measurement $measurement): void
    {
        $this->measurements[$measurement->id()->value] = $measurement;
    }

    public function findById(MeasurementId $id): ?Measurement
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

    public function delete(MeasurementId $id): void
    {
        unset($this->measurements[$id->value]);
    }
}
