<?php

declare(strict_types=1);

namespace App\OpenApi\User;

use OpenApi\Attributes as OA;

/**
 * Definiciones de paths OpenAPI (sin lógica). Los controladores reales están en `App\Http\Controllers\Api\*`.
 */
final class UserApiOperations
{
    #[OA\Post(
        path: '/api/users',
        operationId: 'usersStore',
        summary: 'Crear usuario',
        tags: ['Users'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'name'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'name', type: 'string', minLength: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Creado',
                content: new OA\JsonContent(ref: '#/components/schemas/UserResponse'),
            ),
            new OA\Response(
                response: 422,
                description: 'Validación',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
            ),
        ]
    )]
    public function usersStore(): void {}

    #[OA\Get(
        path: '/api/users/{id}',
        operationId: 'usersShow',
        summary: 'Obtener usuario',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/UserResponse'),
            ),
            new OA\Response(
                response: 404,
                description: 'No encontrado',
                content: new OA\JsonContent(ref: '#/components/schemas/MessageError'),
            ),
        ]
    )]
    public function usersShow(): void {}

    #[OA\Patch(
        path: '/api/users/{id}',
        operationId: 'usersUpdate',
        summary: 'Actualizar usuario (al menos email o name)',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'name', type: 'string', minLength: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/UserResponse'),
            ),
            new OA\Response(
                response: 404,
                description: 'No encontrado',
                content: new OA\JsonContent(ref: '#/components/schemas/MessageError'),
            ),
            new OA\Response(
                response: 422,
                description: 'Validación',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
            ),
        ]
    )]
    public function usersUpdate(): void {}

    #[OA\Delete(
        path: '/api/users/{id}',
        operationId: 'usersDestroy',
        summary: 'Eliminar usuario',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Sin contenido (idempotente si no existe)'),
        ]
    )]
    public function usersDestroy(): void {}

    #[OA\Post(
        path: '/api/users/{id}/subscriptions',
        operationId: 'usersSubscribe',
        summary: 'Suscribir usuario a una estación',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['weather_station_id'],
                properties: [
                    new OA\Property(property: 'weather_station_id', type: 'string', minLength: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/UserResponse'),
            ),
            new OA\Response(
                response: 404,
                description: 'Usuario no encontrado',
                content: new OA\JsonContent(ref: '#/components/schemas/MessageError'),
            ),
            new OA\Response(
                response: 422,
                description: 'Validación',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
            ),
        ]
    )]
    public function usersSubscribe(): void {}

    #[OA\Delete(
        path: '/api/users/{id}/subscriptions/{weatherStationId}',
        operationId: 'usersUnsubscribe',
        summary: 'Desuscribir usuario de una estación',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'weatherStationId', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OK',
                content: new OA\JsonContent(ref: '#/components/schemas/UserResponse'),
            ),
            new OA\Response(
                response: 404,
                description: 'Usuario no encontrado',
                content: new OA\JsonContent(ref: '#/components/schemas/MessageError'),
            ),
        ]
    )]
    public function usersUnsubscribe(): void {}
}
