<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use MongoDB\Client;
use Tests\Support\InMemoryMeasurementRepository;
use Tests\Support\InMemoryUserRepository;
use Tests\Support\InMemoryWeatherStationRepository;
use WeatherFlow\Domain\Repository\MeasurementRepository;
use WeatherFlow\Domain\Repository\UserRepository;
use WeatherFlow\Domain\Repository\WeatherStationRepository;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if ($this->shouldUseMongoForUserRepository()) {
            $this->truncateMongoUsersCollection();
            $this->truncateMongoStationsCollection();
            $this->truncateMongoMeasurementsCollection();

            return;
        }

        $this->app->singleton(
            UserRepository::class,
            fn (): InMemoryUserRepository => new InMemoryUserRepository,
        );
        $this->app->singleton(
            WeatherStationRepository::class,
            fn (): InMemoryWeatherStationRepository => new InMemoryWeatherStationRepository,
        );
        $this->app->singleton(
            MeasurementRepository::class,
            fn (): InMemoryMeasurementRepository => new InMemoryMeasurementRepository,
        );
    }

    /**
     * Cuando `config('weatherflow.testing.use_mongo')` es true, los Feature tests usan
     * MongoUserRepository + MongoWeatherStationRepository. Origen: `WEATHERFLOW_TEST_USE_MONGO`
     * en `.env.testing` (merge en `tests/bootstrap.php`).
     */
    private function shouldUseMongoForUserRepository(): bool
    {
        return (bool) config('weatherflow.testing.use_mongo');
    }

    private function truncateMongoUsersCollection(): void
    {
        $client = $this->app->make(Client::class);
        $database = (string) config('database.mongodb.database');
        $client->selectDatabase($database)->selectCollection('users')->deleteMany([]);
    }

    private function truncateMongoStationsCollection(): void
    {
        $client = $this->app->make(Client::class);
        $database = (string) config('database.mongodb.database');
        $client->selectDatabase($database)->selectCollection('stations')->deleteMany([]);
    }

    private function truncateMongoMeasurementsCollection(): void
    {
        $client = $this->app->make(Client::class);
        $database = (string) config('database.mongodb.database');
        $client->selectDatabase($database)->selectCollection('measurements')->deleteMany([]);
    }
}
