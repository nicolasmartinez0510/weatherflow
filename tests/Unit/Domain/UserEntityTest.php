<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use WeatherFlow\Domain\Entity\User;
use WeatherFlow\Domain\ValueObject\Email;
use WeatherFlow\Domain\ValueObject\StationId;
use WeatherFlow\Domain\ValueObject\UserId;

final class UserEntityTest extends TestCase
{
    public function test_empty_name_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User name cannot be empty.');

        new User(
            new UserId('user-1'),
            new Email('a@b.com'),
            '',
        );
    }

    public function test_rename_empty_name_throws(): void
    {
        $user = new User(new UserId('user-1'), new Email('a@b.com'), 'Ada');

        $this->expectException(\InvalidArgumentException::class);
        $user->rename('');
    }

    public function test_subscribe_to_station_is_idempotent(): void
    {
        $station = new StationId('st-1');
        $user = new User(new UserId('user-1'), new Email('a@b.com'), 'Ada');

        $user->subscribeToStation($station);
        $user->subscribeToStation($station);

        $this->assertCount(1, $user->subscribedStationIds());
        $this->assertSame('st-1', $user->subscribedStationIds()[0]->value);
    }

    public function test_unsubscribe_removes_station(): void
    {
        $s1 = new StationId('st-1');
        $s2 = new StationId('st-2');
        $user = new User(new UserId('user-1'), new Email('a@b.com'), 'Ada', [$s1, $s2]);

        $user->unsubscribeFromStation($s1);

        $this->assertCount(1, $user->subscribedStationIds());
        $this->assertSame('st-2', $user->subscribedStationIds()[0]->value);
    }

    public function test_change_email_updates_value(): void
    {
        $user = new User(new UserId('user-1'), new Email('old@b.com'), 'Ada');
        $user->changeEmail(new Email('new@b.com'));

        $this->assertSame('new@b.com', $user->email()->value);
    }
}
