<?php

declare(strict_types=1);

namespace WeatherFlow\Domain\ValueObject;

enum AlertType: string
{
    case None = 'Ninguna';
    case Heat = 'Calor extremo';
    case Frost = 'Helada';
    case Storm = 'Tormenta / baja presión';
    case HumidityCritical = 'Humedad crítica';

    public function isAlert(): bool {
        return $this !== self::None;
    }
}
