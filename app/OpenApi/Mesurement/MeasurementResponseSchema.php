<?php

declare(strict_types=1);

namespace App\OpenApi\Mesurement;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MeasurementResponse',
    required: ['id', 'weather_station_id', 'temperature', 'humidity', 'pressure', 'reported_at', 'alert', 'alert_type'],
    properties: [
        new OA\Property(property: 'id', type: 'string'),
        new OA\Property(property: 'weather_station_id', type: 'string'),
        new OA\Property(property: 'temperature', type: 'number', format: 'float'),
        new OA\Property(property: 'humidity', type: 'number', format: 'float'),
        new OA\Property(property: 'pressure', type: 'number', format: 'float'),
        new OA\Property(property: 'reported_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'alert', type: 'boolean'),
        new OA\Property(
            property: 'alert_type',
            type: 'string',
            description: 'Etiqueta de alerta según umbrales de dominio (p. ej. Ninguna, Calor extremo).',
        ),
    ],
)]
final class MeasurementResponseSchema {}
