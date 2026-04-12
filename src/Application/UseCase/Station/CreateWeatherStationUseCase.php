<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\Station;

use Ramsey\Uuid\Uuid;
use WeatherFlow\Application\Exception\UserNotFoundException;
use WeatherFlow\Domain\Entity\WeatherStation;
use WeatherFlow\Domain\Repository\UserRepository;
use WeatherFlow\Domain\Repository\WeatherStationRepository;
use WeatherFlow\Domain\ValueObject\Coordinates;
use WeatherFlow\Domain\ValueObject\StationId;
use WeatherFlow\Domain\ValueObject\StationStatus;
use WeatherFlow\Domain\ValueObject\UserId;

final readonly class CreateWeatherStationUseCase
{
    public function __construct(
        private UserRepository $users,
        private WeatherStationRepository $stations,
    ) {}

    public function execute(
        string $ownerId,
        string $name,
        float $latitude,
        float $longitude,
        string $sensorModel,
        StationStatus $status,
    ): StationResponse {
        if ($this->users->findById(new UserId($ownerId)) === null) {
            throw new UserNotFoundException('User not found.');
        }

        $station = new WeatherStation(
            new StationId(Uuid::uuid4()->toString()),
            $name,
            new Coordinates($latitude, $longitude),
            $sensorModel,
            $status,
            new UserId($ownerId),
        );
        $this->stations->save($station);

        return StationResponse::fromEntity($station);
    }
}
