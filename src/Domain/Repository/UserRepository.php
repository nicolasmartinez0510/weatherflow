<?php

declare(strict_types=1);

namespace WeatherFlow\Domain\Repository;

use WeatherFlow\Domain\Entity\User;
use WeatherFlow\Domain\ValueObject\UserId;

interface UserRepository
{
    public function save(User $user): void;

    public function findById(UserId $id): ?User;

    public function delete(UserId $id): void;
}
