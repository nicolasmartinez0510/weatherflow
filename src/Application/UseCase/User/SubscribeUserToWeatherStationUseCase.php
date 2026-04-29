<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\User;

use WeatherFlow\Application\Exception\StationNotFoundException;
use WeatherFlow\Application\Exception\UserNotFoundException;
use WeatherFlow\Domain\Repository\UserRepository;
use WeatherFlow\Domain\Repository\WeatherStationRepository;
use WeatherFlow\Domain\ValueObject\UserId;
use WeatherFlow\Domain\ValueObject\WeatherStationId;

final readonly class SubscribeUserToWeatherStationUseCase
{
    public function __construct(
        private UserRepository $users,
        private WeatherStationRepository $weatherStations,
    ) {
    }

    public function execute(string $userId, string $weatherStationId): UserResponse
    {
        $stationId = new WeatherStationId($weatherStationId);

        $user = $this->users->findById(new UserId($userId));
        if ($user === null) {
            throw new UserNotFoundException();
        }

        if ($this->weatherStations->findById($stationId) === null) {
            throw new StationNotFoundException();
        }

        $user->subscribeToWeatherStation($stationId);
        $this->users->save($user);

        return UserResponse::fromEntity($user);
    }
}
