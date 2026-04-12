<?php

declare(strict_types=1);

namespace WeatherFlow\Domain\ValueObject;

enum StationStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
