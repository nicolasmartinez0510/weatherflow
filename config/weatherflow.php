<?php

declare(strict_types=1);

return [
    'testing' => [
        /*
         * Feature tests: usar repositorios Mongo reales vs fakes en memoria.
         * Variable de entorno: WEATHERFLOW_TEST_USE_MONGO (p. ej. en `.env.testing`).
         */
        'use_mongo' => filter_var(
            env('WEATHERFLOW_TEST_USE_MONGO', false),
            FILTER_VALIDATE_BOOL
        ),
    ],
];
