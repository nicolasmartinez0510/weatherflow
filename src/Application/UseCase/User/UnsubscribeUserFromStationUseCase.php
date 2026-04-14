<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\User;

use WeatherFlow\Application\Exception\UserNotFoundException;
use WeatherFlow\Domain\Repository\UserRepository;
use WeatherFlow\Domain\ValueObject\StationId;
use WeatherFlow\Domain\ValueObject\UserId;

final readonly class UnsubscribeUserFromStationUseCase
{
    public function __construct(
        private UserRepository $users,
    ) {
    }

    public function execute(string $userId, string $stationId): UserResponse
    {
        $user = $this->users->findById(new UserId($userId));
        if ($user === null) {
            throw new UserNotFoundException();
        }

        $user->unsubscribeFromStation(new StationId($stationId));
        $this->users->save($user);

        return UserResponse::fromEntity($user);
    }
}
