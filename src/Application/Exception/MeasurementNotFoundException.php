<?php

declare(strict_types=1);

namespace WeatherFlow\Application\Exception;

final class MeasurementNotFoundException extends WeatherflowException {

    protected $message = 'Measurement not found.';
}
