<?php

declare(strict_types=1);

namespace App\OpenApi\User;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserResponse',
    required: ['id', 'email', 'name', 'subscribed_weather_station_ids'],
    properties: [
        new OA\Property(property: 'id', type: 'string', example: '507f1f77bcf86cd799439011'),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(
            property: 'subscribed_weather_station_ids',
            type: 'array',
            items: new OA\Items(type: 'string'),
        ),
    ],
)]
final class UserResponseSchema {}
