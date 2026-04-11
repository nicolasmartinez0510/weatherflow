<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use MongoDB\Client;
use WeatherFlow\Domain\Repository\UserRepository;
use WeatherFlow\Infrastructure\Persistence\Mongo\MongoUserRepository;

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
    }

    public function boot(): void
    {
        //
    }
}
