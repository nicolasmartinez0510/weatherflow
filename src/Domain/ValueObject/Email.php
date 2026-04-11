<?php

declare(strict_types=1);

namespace WeatherFlow\Domain\ValueObject;

final readonly class Email
{
    public string $value;

    public function __construct(
        string $value,
    ) {
        $trimmed = trim($value);
        if ($trimmed === '') {
            throw new \InvalidArgumentException('Email cannot be empty.');
        }
        if (filter_var($trimmed, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('Invalid email format.');
        }
        $this->value = $trimmed;
    }
}
