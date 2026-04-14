<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\Measurement;

use WeatherFlow\Application\Exception\MeasurementNotFoundException;
use WeatherFlow\Domain\Repository\MeasurementRepository;
use WeatherFlow\Domain\ValueObject\MeasurementId;

final readonly class GetMeasurementUseCase
{
    public function __construct(
        private MeasurementRepository $measurements,
    ) {}

    public function execute(string $id): MeasurementResponse
    {
        $measurement = $this->measurements->findById(new MeasurementId($id));
        if ($measurement === null) {
            throw new MeasurementNotFoundException();
        }

        return MeasurementResponse::fromEntity($measurement);
    }
}
