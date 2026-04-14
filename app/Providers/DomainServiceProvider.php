<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use MongoDB\Client;
use WeatherFlow\Domain\Repository\MeasurementRepository;
use WeatherFlow\Domain\Repository\UserRepository;
use WeatherFlow\Domain\Repository\WeatherStationRepository;
use WeatherFlow\Domain\Service\MeasurementAlertEvaluator;
use WeatherFlow\Infrastructure\Persistence\Mongo\MongoMeasurementRepository;
use WeatherFlow\Infrastructure\Persistence\Mongo\MongoUserRepository;
use WeatherFlow\Infrastructure\Persistence\Mongo\MongoWeatherStationRepository;

/**
 * Binds domain ports to infrastructure adapters (repositories, gateways, etc.).
 */
class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Client::class, function (): Client {
            $uri = (string) config('database.mongodb.uri');

            return new Client($uri);
        });

        $this->app->singleton(UserRepository::class, function ($app): UserRepository {
            return new MongoUserRepository(
                $app->make(Client::class),
                (string) config('database.mongodb.database'),
            );
        });

        $this->app->singleton(WeatherStationRepository::class, function ($app): WeatherStationRepository {
            return new MongoWeatherStationRepository(
                $app->make(Client::class),
                (string) config('database.mongodb.database'),
            );
        });

        $this->app->singleton(MeasurementAlertEvaluator::class, fn (): MeasurementAlertEvaluator => new MeasurementAlertEvaluator);

        $this->app->singleton(MeasurementRepository::class, function ($app): MeasurementRepository {
            return new MongoMeasurementRepository(
                $app->make(Client::class),
                (string) config('database.mongodb.database'),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
