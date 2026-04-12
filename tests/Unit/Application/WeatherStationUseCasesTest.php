<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryUserRepository;
use Tests\Support\InMemoryWeatherStationRepository;
use WeatherFlow\Application\Exception\StationNotFoundException;
use WeatherFlow\Application\Exception\UserNotFoundException;
use WeatherFlow\Application\UseCase\Station\CreateWeatherStationUseCase;
use WeatherFlow\Application\UseCase\Station\DeleteWeatherStationUseCase;
use WeatherFlow\Application\UseCase\Station\GetWeatherStationUseCase;
use WeatherFlow\Application\UseCase\Station\UpdateWeatherStationUseCase;
use WeatherFlow\Domain\Entity\User;
use WeatherFlow\Domain\Entity\WeatherStation;
use WeatherFlow\Domain\ValueObject\Coordinates;
use WeatherFlow\Domain\ValueObject\Email;
use WeatherFlow\Domain\ValueObject\StationId;
use WeatherFlow\Domain\ValueObject\StationStatus;
use WeatherFlow\Domain\ValueObject\UserId;

final class WeatherStationUseCasesTest extends TestCase
{
    private InMemoryUserRepository $users;

    private InMemoryWeatherStationRepository $stations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->users = new InMemoryUserRepository;
        $this->stations = new InMemoryWeatherStationRepository;
    }

    public function test_create_station_requires_existing_owner(): void
    {
        $create = new CreateWeatherStationUseCase($this->users, $this->stations);

        $this->expectException(UserNotFoundException::class);
        $create->execute(
            'missing-user',
            'Central',
            -34.0,
            -58.0,
            'DHT22',
            StationStatus::Active,
        );
    }

    public function test_create_and_get_station_with_inactive_status(): void
    {
        $this->users->save(new User(
            new UserId('u-1'),
            new Email('o@example.com'),
            'Owner',
        ));

        $create = new CreateWeatherStationUseCase($this->users, $this->stations);
        $created = $create->execute(
            'u-1',
            'Estación Central',
            -34.6,
            -58.4,
            'DHT22',
            StationStatus::Inactive,
        );

        $this->assertSame('u-1', $created->ownerId);
        $this->assertSame('Estación Central', $created->name);
        $this->assertSame(-34.6, $created->latitude);
        $this->assertSame(-58.4, $created->longitude);
        $this->assertSame('inactive', $created->status);

        $get = new GetWeatherStationUseCase($this->stations);
        $loaded = $get->execute($created->id);

        $this->assertSame($created->id, $loaded->id);
        $this->assertSame('DHT22', $loaded->sensorModel);
    }

    public function test_get_missing_station_throws(): void
    {
        $get = new GetWeatherStationUseCase($this->stations);

        $this->expectException(StationNotFoundException::class);
        $get->execute('missing-id');
    }

    public function test_update_throws_when_station_missing(): void
    {
        $update = new UpdateWeatherStationUseCase($this->stations);

        $this->expectException(StationNotFoundException::class);
        $update->execute('missing-station', 'N', null, null, null, null);
    }

    public function test_update_sensor_model_only(): void
    {
        $this->stations->save(new WeatherStation(
            new StationId('st-1'),
            'X',
            new Coordinates(1.0, 2.0),
            'Old',
            StationStatus::Active,
            new UserId('u-1'),
        ));

        $update = new UpdateWeatherStationUseCase($this->stations);
        $updated = $update->execute('st-1', null, null, null, 'New', null);

        $this->assertSame('New', $updated->sensorModel);
        $this->assertSame(1.0, $updated->latitude);
        $this->assertSame('X', $updated->name);
    }

    public function test_update_status_only(): void
    {
        $this->stations->save(new WeatherStation(
            new StationId('st-1'),
            'X',
            new Coordinates(0.0, 0.0),
            'A',
            StationStatus::Active,
            new UserId('u-1'),
        ));

        $update = new UpdateWeatherStationUseCase($this->stations);
        $updated = $update->execute('st-1', null, null, null, null, StationStatus::Inactive);

        $this->assertSame('inactive', $updated->status);
    }

    public function test_update_name_and_location(): void
    {
        $this->stations->save(new WeatherStation(
            new StationId('st-1'),
            'Old',
            new Coordinates(-10.0, -20.0),
            'A',
            StationStatus::Active,
            new UserId('u-1'),
        ));

        $update = new UpdateWeatherStationUseCase($this->stations);
        $updated = $update->execute('st-1', 'New', -1.0, -2.0, null, null);

        $this->assertSame('New', $updated->name);
        $this->assertSame(-1.0, $updated->latitude);
        $this->assertSame(-2.0, $updated->longitude);
    }

    public function test_update_latitude_without_longitude_throws(): void
    {
        $this->stations->save(new WeatherStation(
            new StationId('st-1'),
            'X',
            new Coordinates(0.0, 0.0),
            'A',
            StationStatus::Active,
            new UserId('u-1'),
        ));

        $update = new UpdateWeatherStationUseCase($this->stations);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Latitude and longitude must be updated together.');
        $update->execute('st-1', null, 1.0, null, null, null);
    }

    public function test_update_with_no_fields_throws(): void
    {
        $this->stations->save(new WeatherStation(
            new StationId('st-1'),
            'X',
            new Coordinates(0.0, 0.0),
            'A',
            StationStatus::Active,
            new UserId('u-1'),
        ));

        $update = new UpdateWeatherStationUseCase($this->stations);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one field must be provided.');
        $update->execute('st-1', null, null, null, null, null);
    }

    public function test_delete_station(): void
    {
        $this->stations->save(new WeatherStation(
            new StationId('st-1'),
            'X',
            new Coordinates(0.0, 0.0),
            'A',
            StationStatus::Active,
            new UserId('u-1'),
        ));

        $delete = new DeleteWeatherStationUseCase($this->stations);
        $delete->execute('st-1');

        $get = new GetWeatherStationUseCase($this->stations);
        $this->expectException(StationNotFoundException::class);
        $get->execute('st-1');
    }
}
