<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\Measurement;

use DateTimeImmutable;
use InvalidArgumentException;
use WeatherFlow\Application\Exception\MeasurementNotFoundException;
use WeatherFlow\Domain\Repository\MeasurementRepository;
use WeatherFlow\Domain\Service\MeasurementAlertEvaluator;
use WeatherFlow\Domain\ValueObject\Humidity;
use WeatherFlow\Domain\ValueObject\MeasurementId;

final readonly class UpdateMeasurementUseCase
{
    public function __construct(
        private MeasurementRepository $measurements,
        private MeasurementAlertEvaluator $alertEvaluator,
    ) {}

    public function execute(
        string $id,
        ?float $temperatureCelsius,
        ?float $humidityPercent,
        ?float $pressureHpa,
        ?DateTimeImmutable $reportedAt,
    ): MeasurementResponse {
        if ($temperatureCelsius === null && $humidityPercent === null && $pressureHpa === null && $reportedAt === null) {
            throw new InvalidArgumentException('At least one field must be provided.');
        }

        $measurement = $this->measurements->findById(new MeasurementId($id));
        if ($measurement === null) {
            throw new MeasurementNotFoundException();
        }

        $nextTemp = $temperatureCelsius ?? $measurement->temperatureCelsius();
        $nextHumidity = $humidityPercent !== null ? new Humidity($humidityPercent) : $measurement->humidity();
        $nextPressure = $pressureHpa ?? $measurement->pressureHpa();
        $nextReported = $reportedAt ?? $measurement->reportedAt();

        $alertType = $this->alertEvaluator->evaluate(
            $nextTemp,
            $nextHumidity->percent,
            $nextPressure,
        );

        $measurement->applyClimateState(
            $nextTemp,
            $nextHumidity,
            $nextPressure,
            $nextReported,
            $alertType->isAlert(),
            $alertType->value,
        );

        $this->measurements->save($measurement);

        return MeasurementResponse::fromEntity($measurement);
    }
}
