<?php

declare(strict_types=1);

namespace WeatherFlow\Domain\ValueObject;

final readonly class StationId
{
    public function __construct(
        public string $value,
    ) {
        if ($value === '') {
            throw new \InvalidArgumentException('StationId cannot be empty.');
        }
    }
}
