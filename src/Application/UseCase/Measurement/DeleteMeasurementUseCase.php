<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\Measurement;

use WeatherFlow\Domain\Repository\MeasurementRepository;
use WeatherFlow\Domain\ValueObject\MeasurementId;

final readonly class DeleteMeasurementUseCase
{
    public function __construct(
        private MeasurementRepository $measurements,
    ) {}

    public function execute(string $id): void
    {
        $this->measurements->delete(new MeasurementId($id));
    }
}
