<?php

declare(strict_types=1);

namespace Tests\Support;

use WeatherFlow\Domain\Entity\User;
use WeatherFlow\Domain\Repository\UserRepository;
use WeatherFlow\Domain\ValueObject\Id;
use WeatherFlow\Domain\ValueObject\UserId;

final class InMemoryUserRepository implements UserRepository
{
    /** @var array<string, User> */
    private array $users = [];

    public function save(User $user): void
    {
        $this->users[$user->id()->value] = $user;
    }

    public function findById(Id $id): ?User
    {
        return $this->users[$id->value] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->users);
    }

    public function delete(Id $id): void
    {
        unset($this->users[$id->value]);
    }
}
