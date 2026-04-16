<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    openapi: '3.0.0',
    info: new OA\Info(
        version: '1.0.0',
        title: 'WeatherFlow API',
        description: 'Weatherflow API para gestión de usuarios, estaciones meteorológicas y mediciones.',
    ),
    tags: [
        new OA\Tag(name: 'Users', description: 'Usuarios y suscripciones a estaciones'),
        new OA\Tag(name: 'Weather Stations', description: 'Estaciones meteorológicas'),
        new OA\Tag(name: 'Measurements', description: 'Mediciones'),
    ],
)]
final class OpenApiDefinition {}
