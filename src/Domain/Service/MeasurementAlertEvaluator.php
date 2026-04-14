<?php

declare(strict_types=1);

namespace WeatherFlow\Domain\Service;

use WeatherFlow\Domain\ValueObject\AlertType;

final class MeasurementAlertEvaluator
{
    public function evaluate(
        float $temperatureCelsius,
        float $humidityPercent,
        float $pressureHpa,
    ): AlertType {
        if ($temperatureCelsius > 40.0) {
            return AlertType::Heat;
        }
        if ($temperatureCelsius < 0.0) {
            return AlertType::Frost;
        }
        if ($pressureHpa < 980.0) {
            return AlertType::Storm;
        }
        if ($humidityPercent > 90.0) {
            return AlertType::HumidityCritical;
        }

        return AlertType::None;
    }
}
