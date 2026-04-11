<?php

declare(strict_types=1);

/*
| PHPUnit loads phpunit.xml first (APP_ENV=testing, cache/session, etc.), then this file.
| We merge .env.testing without overriding those keys so Mongo and WeatherFlow flags apply.
*/

$basePath = dirname(__DIR__);

require $basePath.'/vendor/autoload.php';

$envTesting = $basePath.'/.env.testing';
if (is_file($envTesting)) {
    Dotenv\Dotenv::createImmutable($basePath, '.env.testing')->safeLoad();
}
