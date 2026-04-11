<?php

declare(strict_types=1);

namespace WeatherFlow\Domain\ValueObject;

final readonly class UserId
{
    public function __construct(
        public string $value,
    ) {
        if ($value === '') {
            throw new \InvalidArgumentException('UserId cannot be empty.');
        }
    }
}
