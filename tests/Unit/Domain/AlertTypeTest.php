<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use WeatherFlow\Domain\ValueObject\AlertType;

final class AlertTypeTest extends TestCase
{
    public function test_is_alert(): void
    {
        $this->assertFalse(AlertType::None->isAlert());
        $this->assertTrue(AlertType::Heat->isAlert());
    }
}
