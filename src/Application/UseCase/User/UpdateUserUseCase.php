<?php

declare(strict_types=1);

namespace WeatherFlow\Application\UseCase\User;

use WeatherFlow\Application\Exception\UserNotFoundException;
use WeatherFlow\Domain\Repository\UserRepository;
use WeatherFlow\Domain\ValueObject\Email;
use WeatherFlow\Domain\ValueObject\UserId;

final readonly class UpdateUserUseCase
{
    public function __construct(
        private UserRepository $users,
    ) {
    }

    public function execute(string $id, ?string $name, ?string $email): UserResponse
    {
        if ($name === null && $email === null) {
            throw new \InvalidArgumentException('At least one of name or email must be provided.');
        }

        $user = $this->users->findById(new UserId($id));
        if ($user === null) {
            throw new UserNotFoundException('User not found.');
        }

        if ($name !== null) {
            $user->rename($name);
        }
        if ($email !== null) {
            $user->changeEmail(new Email($email));
        }

        $this->users->save($user);

        return UserResponse::fromEntity($user);
    }
}
