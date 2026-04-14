<?php

declare(strict_types=1);

namespace WeatherFlow\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Relative humidity as percentage 0–100 (inclusive).
 */
final readonly class Humidity
{
    public function __construct(
        public float $percent,
    ) {
        if ($percent < 0.0 || $percent > 100.0) {
            throw new InvalidArgumentException('Humidity must be between 0 and 100 percent.');
        }
    }
}
