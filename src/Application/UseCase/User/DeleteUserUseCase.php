<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\User;

use WeatherFlow\Domain\Repository\UserRepository;
use WeatherFlow\Domain\ValueObject\UserId;

final readonly class DeleteUserUseCase
{
    public function __construct(
        private UserRepository $users,
    ) {
    }

    public function execute(string $id): void
    {
        $this->users->delete(new UserId($id));
    }
}
