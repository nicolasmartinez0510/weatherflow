<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\User;

use WeatherFlow\Domain\Repository\UserRepository;

final readonly class ListUsersUseCase
{
    public function __construct(
        private UserRepository $users,
    ) {
    }

    /**
     * @return list<UserResponse>
     */
    public function execute(): array
    {
        return array_map(
            static fn ($user) => UserResponse::fromEntity($user),
            $this->users->findAll(),
        );
    }
}
