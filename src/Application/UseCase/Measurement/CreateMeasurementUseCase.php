<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\Measurement;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use WeatherFlow\Application\Exception\StationNotFoundException;
use WeatherFlow\Domain\Entity\Measurement;
use WeatherFlow\Domain\Repository\MeasurementRepository;
use WeatherFlow\Domain\Repository\WeatherStationRepository;
use WeatherFlow\Domain\Service\MeasurementAlertEvaluator;
use WeatherFlow\Domain\ValueObject\Humidity;
use WeatherFlow\Domain\ValueObject\MeasurementId;
use WeatherFlow\Domain\ValueObject\StationId;

final readonly class CreateMeasurementUseCase
{
    public function __construct(
        private WeatherStationRepository $stations,
        private MeasurementRepository $measurements,
        private MeasurementAlertEvaluator $alertEvaluator,
    ) {}

    public function execute(
        string $stationId,
        float $temperatureCelsius,
        float $humidityPercent,
        float $pressureHpa,
        DateTimeImmutable $reportedAt,
    ): MeasurementResponse {
        if ($this->stations->findById(new StationId($stationId)) === null) {
            throw new StationNotFoundException();
        }

        $humidity = new Humidity($humidityPercent);
        $alertType = $this->alertEvaluator->evaluate($temperatureCelsius, $humidityPercent, $pressureHpa);

        $measurement = new Measurement(
            new MeasurementId(Uuid::uuid4()->toString()),
            new StationId($stationId),
            $temperatureCelsius,
            $humidity,
            $pressureHpa,
            $reportedAt,
            $alertType->isAlert(),
            $alertType->value,
        );

        $this->measurements->save($measurement);

        return MeasurementResponse::fromEntity($measurement);
    }
}
