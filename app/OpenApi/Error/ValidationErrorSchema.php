<?php

declare(strict_types=1);

namespace App\OpenApi\Error;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ValidationError',
    properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(
            property: 'errors',
            description: 'Errores por campo',
            type: 'object',
        ),
    ],
)]
final class ValidationErrorSchema {}
