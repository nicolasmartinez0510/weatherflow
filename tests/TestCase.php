<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use MongoDB\Client;
use Tests\Support\InMemoryUserRepository;
use WeatherFlow\Domain\Repository\UserRepository;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if ($this->shouldUseMongoForUserRepository()) {
            $this->truncateMongoUsersCollection();

            return;
        }

        $this->app->singleton(
            UserRepository::class,
            fn (): InMemoryUserRepository => new InMemoryUserRepository,
        );
    }

    /**
     * When WEATHERFLOW_TEST_USE_MONGO=true, Feature tests use MongoUserRepository.
     * Prefer MONGODB_URI / MONGODB_DATABASE / this flag in `.env.testing` (merged via tests/bootstrap.php).
     */
    private function shouldUseMongoForUserRepository(): bool
    {
        return filter_var(
            env('WEATHERFLOW_TEST_USE_MONGO', false),
            FILTER_VALIDATE_BOOL,
        );
    }

    private function truncateMongoUsersCollection(): void
    {
        $client = $this->app->make(Client::class);
        $database = (string) config('database.mongodb.database');
        $client->selectDatabase($database)->selectCollection('users')->deleteMany([]);
    }
}
