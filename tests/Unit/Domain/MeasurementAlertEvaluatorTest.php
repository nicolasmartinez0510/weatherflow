<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use WeatherFlow\Domain\Service\MeasurementAlertEvaluator;
use WeatherFlow\Domain\ValueObject\AlertType;

final class MeasurementAlertEvaluatorTest extends TestCase
{
    private MeasurementAlertEvaluator $evaluator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluator = new MeasurementAlertEvaluator;
    }

    public function test_heat_threshold_strictly_above_40(): void
    {
        $r = $this->evaluator->evaluate(40.0, 50.0, 1000.0);
        $this->assertSame(AlertType::None, $r);
        $this->assertFalse($r->isAlert());

        $r2 = $this->evaluator->evaluate(40.1, 50.0, 1000.0);
        $this->assertSame(AlertType::Heat, $r2);
        $this->assertTrue($r2->isAlert());
    }

    public function test_frost_threshold_strictly_below_zero(): void
    {
        $r = $this->evaluator->evaluate(0.0, 50.0, 1000.0);
        $this->assertFalse($r->isAlert());

        $r2 = $this->evaluator->evaluate(-0.1, 50.0, 1000.0);
        $this->assertSame(AlertType::Frost, $r2);
        $this->assertTrue($r2->isAlert());
    }

    public function test_storm_when_pressure_strictly_below_980(): void
    {
        $r = $this->evaluator->evaluate(20.0, 50.0, 980.0);
        $this->assertFalse($r->isAlert());

        $r2 = $this->evaluator->evaluate(20.0, 50.0, 979.9);
        $this->assertSame(AlertType::Storm, $r2);
        $this->assertTrue($r2->isAlert());
    }

    public function test_humidity_critical_strictly_above_90(): void
    {
        $r = $this->evaluator->evaluate(20.0, 90.0, 1000.0);
        $this->assertFalse($r->isAlert());

        $r2 = $this->evaluator->evaluate(20.0, 90.1, 1000.0);
        $this->assertSame(AlertType::HumidityCritical, $r2);
        $this->assertTrue($r2->isAlert());
    }

    public function test_priority_heat_before_other_rules(): void
    {
        $r = $this->evaluator->evaluate(45.0, 95.0, 970.0);
        $this->assertSame(AlertType::Heat, $r);
        $this->assertTrue($r->isAlert());
    }

    public function test_no_alert_when_all_normal(): void
    {
        $r = $this->evaluator->evaluate(25.0, 60.0, 1013.0);
        $this->assertSame(AlertType::None, $r);
        $this->assertFalse($r->isAlert());
    }
}
