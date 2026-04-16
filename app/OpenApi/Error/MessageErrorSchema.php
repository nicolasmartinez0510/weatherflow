<?php

declare(strict_types=1);

namespace App\OpenApi\Error;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MessageError',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'User not found.'),
    ],
)]
final class MessageErrorSchema {}
