<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\User;

use WeatherFlow\Application\Exception\UserNotFoundException;
use WeatherFlow\Domain\Repository\UserRepository;
use WeatherFlow\Domain\ValueObject\UserId;
use WeatherFlow\Domain\ValueObject\WeatherStationId;

final readonly class UnsubscribeUserFromWeatherStationUseCase
{
    public function __construct(
        private UserRepository $users,
    ) {
    }

    public function execute(string $userId, string $weatherStationId): UserResponse
    {
        $user = $this->users->findById(new UserId($userId));
        if ($user === null) {
            throw new UserNotFoundException();
        }

        $user->unsubscribeFromWeatherStation(new WeatherStationId($weatherStationId));
        $this->users->save($user);

        return UserResponse::fromEntity($user);
    }
}
