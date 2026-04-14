<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WeatherFlow\Domain\Entity\Measurement;
use WeatherFlow\Domain\Service\MeasurementAlertEvaluator;
use WeatherFlow\Domain\ValueObject\AlertType;
use WeatherFlow\Domain\ValueObject\Humidity;
use WeatherFlow\Domain\ValueObject\MeasurementId;
use WeatherFlow\Domain\ValueObject\StationId;

final class MeasurementEntityTest extends TestCase
{
    public function test_rejects_non_positive_pressure(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Measurement(
            new MeasurementId('m-1'),
            new StationId('s-1'),
            20.0,
            new Humidity(50.0),
            0.0,
            new DateTimeImmutable('2026-01-01T12:00:00+00:00'),
            false,
            AlertType::None->value,
        );
    }

    public function test_apply_climate_state_updates_fields(): void
    {
        $evaluator = new MeasurementAlertEvaluator;
        $alertType = $evaluator->evaluate(41.0, 50.0, 1000.0);

        $m = new Measurement(
            new MeasurementId('m-1'),
            new StationId('s-1'),
            20.0,
            new Humidity(50.0),
            1000.0,
            new DateTimeImmutable('2026-01-01T12:00:00+00:00'),
            false,
            AlertType::None->value,
        );

        $nextReported = new DateTimeImmutable('2026-01-02T12:00:00+00:00');
        $m->applyClimateState(41.0, new Humidity(50.0), 1000.0, $nextReported, $alertType->isAlert(), $alertType->value);

        $this->assertSame(41.0, $m->temperatureCelsius());
        $this->assertTrue($m->alert());
        $this->assertSame(AlertType::Heat->value, $m->alertType());
        $this->assertSame($nextReported, $m->reportedAt());
    }
}
