<?php

declare(strict_types=1);

namespace WeatherFlow\Application\Exception;

final class StationNotFoundException extends WeatherFlowException {

    protected $message = 'Station not found';

}
