<?php

declare(strict_types=1);

namespace App\OpenApi\Mesurement;

use OpenApi\Attributes as OA;

final class MeasurementApiOperations
{
    #[OA\Get(
        path: '/api/weather-stations/{weatherStationId}/measurements',
        operationId: 'measurementsIndexByWeatherStation',
        summary: 'Listar mediciones de una estación',
        tags: ['Measurements'],
        parameters: [
            new OA\Parameter(name: 'weatherStationId', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/MeasurementResponse'),
                ),
            ),
            new OA\Response(
                response: 404,
                description: 'Estación no encontrada',
                content: new OA\JsonContent(
                    example: ['message' => 'Weather station not found.'],
                    allOf: [new OA\Schema(ref: '#/components/schemas/MessageError')],
                ),
            ),
        ]
    )]
    public function measurementsByStation(): void {}

    #[OA\Post(
        path: '/api/measurements',
        operationId: 'measurementsStore',
        summary: 'Crear medición (evalúa alertas)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['weather_station_id', 'temperature', 'humidity', 'pressure', 'reported_at'],
                properties: [
                    new OA\Property(property: 'weather_station_id', type: 'string', minLength: 1),
                    new OA\Property(property: 'temperature', type: 'number', format: 'float'),
                    new OA\Property(property: 'humidity', type: 'number', format: 'float', maximum: 100, minimum: 0),
                    new OA\Property(property: 'pressure', type: 'number', format: 'float', minimum: 0.01),
                    new OA\Property(property: 'reported_at', type: 'string', format: 'date-time'),
                ]
            )
        ),
        tags: ['Measurements'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Creado',
                content: new OA\JsonContent(ref: '#/components/schemas/MeasurementResponse'),
            ),
            new OA\Response(
                response: 404,
                description: 'Estación no encontrada',
                content: new OA\JsonContent(
                    example: ['message' => 'Weather station not found.'],
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
    public function measurementsStore(): void {}

    #[OA\Get(
        path: '/api/measurements',
        operationId: 'measurementsIndex',
        summary: 'Historial con filtros opcionales',
        tags: ['Measurements'],
        parameters: [
            new OA\Parameter(
                name: 'station_name',
                description: 'Filtrar por nombre de estación (join con colección stations)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', minLength: 1),
            ),
            new OA\Parameter(
                name: 'min_temperature',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'number', format: 'float'),
            ),
            new OA\Parameter(
                name: 'max_temperature',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'number', format: 'float'),
            ),
            new OA\Parameter(
                name: 'alerts_only',
                description: 'Solo mediciones con alerta',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'boolean'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/MeasurementResponse'),
                ),
            ),
            new OA\Response(
                response: 422,
                description: 'Validación (p. ej. min_temperature > max_temperature)',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
            ),
        ]
    )]
    public function measurementsIndex(): void {}

    #[OA\Get(
        path: '/api/measurements/{id}',
        operationId: 'measurementsShow',
        summary: 'Obtener medición',
        tags: ['Measurements'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/MeasurementResponse'),
            ),
            new OA\Response(
                response: 404,
                description: 'No encontrado',
                content: new OA\JsonContent(
                    example: ['message' => 'Measurement not found.'],
                    allOf: [new OA\Schema(ref: '#/components/schemas/MessageError')],
                ),
            ),
        ]
    )]
    public function measurementsShow(): void {}

    #[OA\Patch(
        path: '/api/measurements/{id}',
        operationId: 'measurementsUpdate',
        summary: 'Actualizar medición',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'temperature', type: 'number', format: 'float'),
                    new OA\Property(property: 'humidity', type: 'number', format: 'float', maximum: 100, minimum: 0),
                    new OA\Property(property: 'pressure', type: 'number', format: 'float', minimum: 0.01),
                    new OA\Property(property: 'reported_at', type: 'string', format: 'date-time'),
                ]
            )
        ),
        tags: ['Measurements'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/MeasurementResponse'),
            ),
            new OA\Response(
                response: 404,
                description: 'No encontrado',
                content: new OA\JsonContent(
                    example: ['message' => 'Measurement not found.'],
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
    public function measurementsUpdate(): void {}

    #[OA\Delete(
        path: '/api/measurements/{id}',
        operationId: 'measurementsDestroy',
        summary: 'Eliminar medición',
        tags: ['Measurements'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Sin contenido (idempotente si no existe)'),
        ]
    )]
    public function measurementsDestroy(): void {}
}
