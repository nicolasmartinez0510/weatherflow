<?php

declare(strict_types=1);

// Composer’s classmap can omit PHPUnit 12’s CodeCoverageInitializationStatus enum; preload fixes PHPUnit runs.
$enum = __DIR__.'/../vendor/phpunit/phpunit/src/Runner/CodeCoverageInitializationStatus.php';

if (is_file($enum)) {
    require_once $enum;
}
