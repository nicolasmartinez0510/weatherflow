<?php

declare(strict_types=1);

namespace WeatherFlow\Domain\Repository;

use WeatherFlow\Domain\Entity\User;
use WeatherFlow\Domain\Entity\WeatherflowEntity;
use WeatherFlow\Domain\ValueObject\Id;

interface UserRepository
{
    public function save(User $user): void;

    public function findById(Id $id): ?WeatherflowEntity;

    public function delete(Id $id): void;
}
