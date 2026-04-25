<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\WeatherStation;

use Ramsey\Uuid\Uuid;
use WeatherFlow\Application\Exception\UserNotFoundException;
use WeatherFlow\Domain\Entity\WeatherStation;
use WeatherFlow\Domain\Repository\UserRepository;
use WeatherFlow\Domain\Repository\WeatherStationRepository;
use WeatherFlow\Domain\ValueObject\Coordinates;
use WeatherFlow\Domain\ValueObject\WeatherStationId;
use WeatherFlow\Domain\ValueObject\WeatherStationStatus;
use WeatherFlow\Domain\ValueObject\UserId;

final readonly class CreateWeatherStationUseCase
{
    public function __construct(
        private UserRepository $users,
        private WeatherStationRepository $weatherStations,
    ) {}

    public function execute(
        string               $ownerId,
        string               $name,
        float                $latitude,
        float                $longitude,
        string               $sensorModel,
        WeatherStationStatus $status,
    ): WeatherStationResponse {
        if ($this->users->findById(new UserId($ownerId)) === null) {
            throw new UserNotFoundException();
        }

        $station = new WeatherStation(
            new WeatherStationId(Uuid::uuid4()->toString()),
            $name,
            new Coordinates($latitude, $longitude),
            $sensorModel,
            $status,
            new UserId($ownerId),
        );
        $this->weatherStations->save($station);

        return WeatherStationResponse::fromEntity($station);
    }
}
