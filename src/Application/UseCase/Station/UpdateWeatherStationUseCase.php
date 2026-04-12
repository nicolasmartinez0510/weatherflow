<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\Station;

use WeatherFlow\Application\Exception\StationNotFoundException;
use WeatherFlow\Domain\Repository\WeatherStationRepository;
use WeatherFlow\Domain\ValueObject\Coordinates;
use WeatherFlow\Domain\ValueObject\StationId;
use WeatherFlow\Domain\ValueObject\StationStatus;

final readonly class UpdateWeatherStationUseCase
{
    public function __construct(
        private WeatherStationRepository $stations,
    ) {}

    public function execute(
        string $id,
        ?string $name,
        ?float $latitude,
        ?float $longitude,
        ?string $sensorModel,
        ?StationStatus $status,
    ): StationResponse {
        if ($name === null && $latitude === null && $longitude === null && $sensorModel === null && $status === null) {
            throw new \InvalidArgumentException('At least one field must be provided.');
        }

        if (($latitude === null) !== ($longitude === null)) {
            throw new \InvalidArgumentException('Latitude and longitude must be updated together.');
        }

        $station = $this->stations->findById(new StationId($id));
        if ($station === null) {
            throw new StationNotFoundException('Station not found.');
        }

        if ($name !== null) {
            $station->rename($name);
        }
        if ($latitude !== null && $longitude !== null) {
            $station->relocate(new Coordinates($latitude, $longitude));
        }
        if ($sensorModel !== null) {
            $station->changeSensorModel($sensorModel);
        }
        if ($status !== null) {
            $station->setStatus($status);
        }

        $this->stations->save($station);

        return StationResponse::fromEntity($station);
    }
}
