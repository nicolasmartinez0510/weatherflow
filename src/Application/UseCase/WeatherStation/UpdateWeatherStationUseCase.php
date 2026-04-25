<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\WeatherStation;

use InvalidArgumentException;
use WeatherFlow\Application\Exception\StationNotFoundException;
use WeatherFlow\Domain\Repository\WeatherStationRepository;
use WeatherFlow\Domain\ValueObject\Coordinates;
use WeatherFlow\Domain\ValueObject\WeatherStationId;
use WeatherFlow\Domain\ValueObject\WeatherStationStatus;

final readonly class UpdateWeatherStationUseCase
{
    public function __construct(
        private WeatherStationRepository $weatherStations,
    ) {}

    public function execute(
        string                $id,
        ?string               $name,
        ?float                $latitude,
        ?float                $longitude,
        ?string               $sensorModel,
        ?WeatherStationStatus $status,
    ): WeatherStationResponse {
        if ($name === null && $latitude === null && $longitude === null && $sensorModel === null && $status === null) {
            throw new InvalidArgumentException('At least one field must be provided.');
        }

        if (($latitude === null) !== ($longitude === null)) {
            throw new InvalidArgumentException('Latitude and longitude must be updated together.');
        }

        $weatherStation = $this->weatherStations->findById(new WeatherStationId($id));
        if ($weatherStation === null) {
            throw new StationNotFoundException();
        }

        if ($name !== null) {
            $weatherStation->rename($name);
        }
        if ($latitude !== null && $longitude !== null) {
            $weatherStation->relocate(new Coordinates($latitude, $longitude));
        }
        if ($sensorModel !== null) {
            $weatherStation->changeSensorModel($sensorModel);
        }
        if ($status !== null) {
            $weatherStation->setStatus($status);
        }

        $this->weatherStations->save($weatherStation);

        return WeatherStationResponse::fromEntity($weatherStation);
    }
}
