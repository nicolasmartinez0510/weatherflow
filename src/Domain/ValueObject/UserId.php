<?php

declare(strict_types=1);

namespace WeatherFlow\Domain\ValueObject;

use InvalidArgumentException;

final readonly class UserId implements Id
{
    public function __construct(
        public string $value,
    ) {
        if ($value === '') {
            throw new InvalidArgumentException('UserId cannot be empty.');
        }
    }
}
