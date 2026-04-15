<?php

declare(strict_types=1);

namespace WeatherFlow\Domain\ValueObject;

use InvalidArgumentException;

final readonly class StationId implements Id
{
    public function __construct(
        public string $value,
    ) {
        if ($value === '') {
            throw new InvalidArgumentException('StationId cannot be empty.');
        }
    }
}
