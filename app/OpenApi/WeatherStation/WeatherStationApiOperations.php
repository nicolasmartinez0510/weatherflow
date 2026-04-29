<?php

declare(strict_types=1);

namespace App\OpenApi\WeatherStation;

use OpenApi\Attributes as OA;

final class WeatherStationApiOperations
{
    #[OA\Get(
        path: '/api/weather-stations',
        operationId: 'stationsIndex',
        summary: 'Listar estaciones',
        tags: ['Weather Stations'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/WeatherStationResponse'),
                ),
            ),
        ]
    )]
    public function stationsIndex(): void {}

    #[OA\Post(
        path: '/api/weather-stations',
        operationId: 'stationsStore',
        summary: 'Crear estación',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['owner_id', 'name', 'latitude', 'longitude', 'sensor_model'],
                properties: [
                    new OA\Property(property: 'owner_id', type: 'string', minLength: 1),
                    new OA\Property(property: 'name', type: 'string', minLength: 1),
                    new OA\Property(property: 'latitude', type: 'number', format: 'float', maximum: 90, minimum: -90),
                    new OA\Property(property: 'longitude', type: 'number', format: 'float', maximum: 180, minimum: -180),
                    new OA\Property(property: 'sensor_model', type: 'string', minLength: 1),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive']),
                ]
            )
        ),
        tags: ['Weather Stations'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Creado',
                content: new OA\JsonContent(ref: '#/components/schemas/WeatherStationResponse'),
            ),
            new OA\Response(
                response: 404,
                description: 'Dueño (usuario) no encontrado',
                content: new OA\JsonContent(
                    example: ['message' => 'Owner user not found.'],
                    allOf: [new OA\Schema(ref: '#/components/schemas/MessageError')],
                ),
            ),
            new OA\Response(
                response: 422,
                description: 'Validación',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
            ),
        ]
    )]
    public function stationsStore(): void {}

    #[OA\Get(
        path: '/api/weather-stations/{id}',
        operationId: 'stationsShow',
        summary: 'Obtener estación',
        tags: ['Weather Stations'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/WeatherStationResponse'),
            ),
            new OA\Response(
                response: 404,
                description: 'No encontrado',
                content: new OA\JsonContent(
                    example: ['message' => 'Weather station not found.'],
                    allOf: [new OA\Schema(ref: '#/components/schemas/MessageError')],
                ),
            ),
        ]
    )]
    public function stationsShow(): void {}

    #[OA\Patch(
        path: '/api/weather-stations/{id}',
        operationId: 'stationsUpdate',
        summary: 'Actualizar estación (lat/lon en pareja)',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', minLength: 1),
                    new OA\Property(property: 'latitude', type: 'number', format: 'float', maximum: 90, minimum: -90),
                    new OA\Property(property: 'longitude', type: 'number', format: 'float', maximum: 180, minimum: -180),
                    new OA\Property(property: 'sensor_model', type: 'string', minLength: 1),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive']),
                ]
            )
        ),
        tags: ['Weather Stations'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/WeatherStationResponse'),
            ),
            new OA\Response(
                response: 404,
                description: 'No encontrado',
                content: new OA\JsonContent(
                    example: ['message' => 'Weather station not found.'],
                    allOf: [new OA\Schema(ref: '#/components/schemas/MessageError')],
                ),
            ),
            new OA\Response(
                response: 422,
                description: 'Validación o reglas de negocio',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
            ),
        ]
    )]
    public function stationsUpdate(): void {}

    #[OA\Delete(
        path: '/api/weather-stations/{id}',
        operationId: 'stationsDestroy',
        summary: 'Eliminar estación',
        tags: ['Weather Stations'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Sin contenido (idempotente si no existe)'),
        ]
    )]
    public function stationsDestroy(): void {}
}
