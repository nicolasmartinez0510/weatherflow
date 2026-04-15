<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryMeasurementRepository;
use Tests\Support\InMemoryUserRepository;
use Tests\Support\InMemoryWeatherStationRepository;
use WeatherFlow\Application\Exception\MeasurementNotFoundException;
use WeatherFlow\Application\Exception\StationNotFoundException;
use WeatherFlow\Application\UseCase\Measurement\CreateMeasurementUseCase;
use WeatherFlow\Application\UseCase\Measurement\DeleteMeasurementUseCase;
use WeatherFlow\Application\UseCase\Measurement\GetMeasurementUseCase;
use WeatherFlow\Application\UseCase\Measurement\ListMeasurementHistoryUseCase;
use WeatherFlow\Application\UseCase\Measurement\ListMeasurementsByStationUseCase;
use WeatherFlow\Application\UseCase\Measurement\UpdateMeasurementUseCase;
use WeatherFlow\Domain\Entity\User;
use WeatherFlow\Domain\Entity\WeatherStation;
use WeatherFlow\Domain\Service\MeasurementAlertEvaluator;
use WeatherFlow\Domain\ValueObject\Coordinates;
use WeatherFlow\Domain\ValueObject\Email;
use WeatherFlow\Domain\ValueObject\StationId;
use WeatherFlow\Domain\ValueObject\StationStatus;
use WeatherFlow\Domain\ValueObject\UserId;

final class MeasurementUseCasesTest extends TestCase
{
    private InMemoryUserRepository $users;

    private InMemoryWeatherStationRepository $stations;

    private InMemoryMeasurementRepository $measurements;

    private MeasurementAlertEvaluator $alertEvaluator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->users = new InMemoryUserRepository;
        $this->stations = new InMemoryWeatherStationRepository;
        $this->measurements = new InMemoryMeasurementRepository($this->stations);
        $this->alertEvaluator = new MeasurementAlertEvaluator;
    }

    public function test_create_requires_existing_station(): void
    {
        $create = new CreateMeasurementUseCase($this->stations, $this->measurements, $this->alertEvaluator);

        $this->expectException(StationNotFoundException::class);
        $create->execute(
            'missing-station',
            20.0,
            50.0,
            1000.0,
            new DateTimeImmutable('2026-01-01T12:00:00+00:00'),
        );
    }

    public function test_create_sets_alert_type_for_extreme_heat(): void
    {
        $this->seedOwnerAndStation('s-heat');

        $create = new CreateMeasurementUseCase($this->stations, $this->measurements, $this->alertEvaluator);
        $created = $create->execute(
            's-heat',
            41.0,
            50.0,
            1000.0,
            new DateTimeImmutable('2026-01-01T12:00:00+00:00'),
        );

        $this->assertTrue($created->alert);
        $this->assertSame('Calor extremo', $created->alertType);
    }

    public function test_get_missing_measurement_throws(): void
    {
        $get = new GetMeasurementUseCase($this->measurements);

        $this->expectException(MeasurementNotFoundException::class);
        $get->execute('missing-id');
    }

    public function test_update_re_evaluates_alert(): void
    {
        $this->seedOwnerAndStation('s-1');

        $create = new CreateMeasurementUseCase($this->stations, $this->measurements, $this->alertEvaluator);
        $created = $create->execute(
            's-1',
            20.0,
            50.0,
            1000.0,
            new DateTimeImmutable('2026-01-01T12:00:00+00:00'),
        );
        $this->assertFalse($created->alert);

        $update = new UpdateMeasurementUseCase($this->measurements, $this->alertEvaluator);
        $updated = $update->execute($created->id, 41.0, null, null, null);

        $this->assertTrue($updated->alert);
        $this->assertSame('Calor extremo', $updated->alertType);
    }

    public function test_update_throws_when_measurement_missing(): void
    {
        $update = new UpdateMeasurementUseCase($this->measurements, $this->alertEvaluator);

        $this->expectException(MeasurementNotFoundException::class);
        $update->execute('missing', 1.0, null, null, null);
    }

    public function test_update_throws_when_no_fields(): void
    {
        $this->seedOwnerAndStation('s-1');
        $create = new CreateMeasurementUseCase($this->stations, $this->measurements, $this->alertEvaluator);
        $created = $create->execute(
            's-1',
            20.0,
            50.0,
            1000.0,
            new DateTimeImmutable('2026-01-01T12:00:00+00:00'),
        );

        $update = new UpdateMeasurementUseCase($this->measurements, $this->alertEvaluator);

        $this->expectException(InvalidArgumentException::class);
        $update->execute($created->id, null, null, null, null);
    }

    public function test_list_by_station_orders_newest_first(): void
    {
        $this->seedOwnerAndStation('s-list');

        $create = new CreateMeasurementUseCase($this->stations, $this->measurements, $this->alertEvaluator);
        $create->execute(
            's-list',
            20.0,
            50.0,
            1000.0,
            new DateTimeImmutable('2026-01-01T12:00:00+00:00'),
        );
        $create->execute(
            's-list',
            21.0,
            50.0,
            1000.0,
            new DateTimeImmutable('2026-01-02T12:00:00+00:00'),
        );

        $list = new ListMeasurementsByStationUseCase($this->stations, $this->measurements);
        $items = $list->execute('s-list');

        $this->assertCount(2, $items);
        $this->assertSame(21.0, $items[0]->temperature);
        $this->assertSame(20.0, $items[1]->temperature);
    }

    public function test_list_by_station_throws_when_station_missing(): void
    {
        $list = new ListMeasurementsByStationUseCase($this->stations, $this->measurements);

        $this->expectException(StationNotFoundException::class);
        $list->execute('no-station');
    }

    public function test_delete_is_idempotent(): void
    {
        $delete = new DeleteMeasurementUseCase($this->measurements);
        $delete->execute('any-id');
        $this->addToAssertionCount(1);
    }

    public function test_history_filters_by_station_name_temperature_and_alert_only(): void
    {
        $this->seedOwnerAndStation('s-north', 'Northern Station');
        $this->seedOwnerAndStation('s-south', 'Southern Station');

        $create = new CreateMeasurementUseCase($this->stations, $this->measurements, $this->alertEvaluator);
        $create->execute('s-north', 22.0, 50.0, 1000.0, new DateTimeImmutable('2026-01-01T10:00:00+00:00'));
        $create->execute('s-north', 41.0, 50.0, 1000.0, new DateTimeImmutable('2026-01-02T10:00:00+00:00'));
        $create->execute('s-south', 41.0, 50.0, 1000.0, new DateTimeImmutable('2026-01-03T10:00:00+00:00'));

        $history = new ListMeasurementHistoryUseCase($this->measurements);
        $items = $history->execute('north', 30.0, 45.0, true);

        $this->assertCount(1, $items);
        $this->assertSame('s-north', $items[0]->stationId);
        $this->assertSame(41.0, $items[0]->temperature);
        $this->assertTrue($items[0]->alert);
    }

    public function test_history_without_filters_returns_all_newest_first(): void
    {
        $this->seedOwnerAndStation('s-all', 'All Station');
        $create = new CreateMeasurementUseCase($this->stations, $this->measurements, $this->alertEvaluator);
        $create->execute('s-all', 20.0, 50.0, 1000.0, new DateTimeImmutable('2026-01-01T10:00:00+00:00'));
        $create->execute('s-all', 30.0, 50.0, 1000.0, new DateTimeImmutable('2026-01-02T10:00:00+00:00'));

        $history = new ListMeasurementHistoryUseCase($this->measurements);
        $items = $history->execute(null, null, null, false);

        $this->assertCount(2, $items);
        $this->assertSame(30.0, $items[0]->temperature);
        $this->assertSame(20.0, $items[1]->temperature);
    }

    private function seedOwnerAndStation(string $stationId, string $stationName = 'S'): void
    {
        $this->users->save(new User(
            new UserId('owner-1'),
            new Email('o@example.com'),
            'Owner',
        ));

        $this->stations->save(new WeatherStation(
            new StationId($stationId),
            $stationName,
            new Coordinates(0.0, 0.0),
            'DHT22',
            StationStatus::Active,
            new UserId('owner-1'),
        ));
    }
}
