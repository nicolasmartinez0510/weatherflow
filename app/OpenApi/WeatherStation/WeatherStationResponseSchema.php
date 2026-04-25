<?php

declare(strict_types=1);

namespace App\OpenApi\WeatherStation;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'WeatherStationResponse',
    required: ['id', 'name', 'latitude', 'longitude', 'sensor_model', 'status', 'owner_id'],
    properties: [
        new OA\Property(property: 'id', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'latitude', type: 'number', format: 'float'),
        new OA\Property(property: 'longitude', type: 'number', format: 'float'),
        new OA\Property(property: 'sensor_model', type: 'string'),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive']),
        new OA\Property(property: 'owner_id', type: 'string'),
    ],
)]
final class WeatherStationResponseSchema {}
