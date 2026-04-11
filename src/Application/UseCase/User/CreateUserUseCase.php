<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\User;

use Ramsey\Uuid\Uuid;
use WeatherFlow\Domain\Entity\User;
use WeatherFlow\Domain\Repository\UserRepository;
use WeatherFlow\Domain\ValueObject\Email;
use WeatherFlow\Domain\ValueObject\UserId;

final readonly class CreateUserUseCase
{
    public function __construct(
        private UserRepository $users,
    ) {
    }

    public function execute(string $email, string $name): UserResponse
    {
        $user = new User(
            new UserId(Uuid::uuid4()->toString()),
            new Email($email),
            $name,
        );
        $this->users->save($user);

        return UserResponse::fromEntity($user);
    }
}
