<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\User;

use WeatherFlow\Application\Exception\UserNotFoundException;
use WeatherFlow\Domain\Repository\UserRepository;
use WeatherFlow\Domain\ValueObject\UserId;

final readonly class GetUserUseCase
{
    public function __construct(
        private UserRepository $users,
    ) {
    }

    public function execute(string $id): UserResponse
    {
        $user = $this->users->findById(new UserId($id));
        if ($user === null) {
            throw new UserNotFoundException('User not found.');
        }

        return UserResponse::fromEntity($user);
    }
}
