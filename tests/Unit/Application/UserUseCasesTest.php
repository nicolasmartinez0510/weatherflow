<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryUserRepository;
use WeatherFlow\Application\Exception\UserNotFoundException;
use WeatherFlow\Application\UseCase\User\CreateUserUseCase;
use WeatherFlow\Application\UseCase\User\DeleteUserUseCase;
use WeatherFlow\Application\UseCase\User\GetUserUseCase;
use WeatherFlow\Application\UseCase\User\ListUsersUseCase;
use WeatherFlow\Application\UseCase\User\SubscribeUserToWeatherStationUseCase;
use WeatherFlow\Application\UseCase\User\UnsubscribeUserFromWeatherStationUseCase;
use WeatherFlow\Application\UseCase\User\UpdateUserUseCase;
use WeatherFlow\Domain\Entity\User;
use WeatherFlow\Domain\ValueObject\Email;
use WeatherFlow\Domain\ValueObject\UserId;

final class UserUseCasesTest extends TestCase
{
    private InMemoryUserRepository $users;

    protected function setUp(): void
    {
        parent::setUp();
        $this->users = new InMemoryUserRepository;
    }

    public function test_create_and_get_user(): void
    {
        $create = new CreateUserUseCase($this->users);
        $created = $create->execute('ada@example.com', 'Ada');

        $get = new GetUserUseCase($this->users);
        $loaded = $get->execute($created->id);

        $this->assertSame($created->id, $loaded->id);
        $this->assertSame('ada@example.com', $loaded->email);
        $this->assertSame('Ada', $loaded->name);
        $this->assertSame([], $loaded->subscribedWeatherStationIds);
    }

    public function test_get_missing_user_throws(): void
    {
        $get = new GetUserUseCase($this->users);

        $this->expectException(UserNotFoundException::class);
        $get->execute('missing-id');
    }

    public function test_list_users_returns_all_users(): void
    {
        $this->users->save(new User(
            new UserId('u-1'),
            new Email('a@example.com'),
            'A',
        ));
        $this->users->save(new User(
            new UserId('u-2'),
            new Email('b@example.com'),
            'B',
        ));

        $list = new ListUsersUseCase($this->users);
        $items = $list->execute();

        $this->assertCount(2, $items);
        $this->assertSame('u-1', $items[0]->id);
        $this->assertSame('u-2', $items[1]->id);
    }

    public function test_update_user_name_and_email(): void
    {
        $this->users->save(new User(
            new UserId('u-1'),
            new Email('old@example.com'),
            'Old',
        ));

        $update = new UpdateUserUseCase($this->users);
        $updated = $update->execute('u-1', 'New', 'new@example.com');

        $this->assertSame('New', $updated->name);
        $this->assertSame('new@example.com', $updated->email);
    }

    public function test_update_name_only_preserves_email(): void
    {
        $this->users->save(new User(
            new UserId('u-1'),
            new Email('keep@example.com'),
            'Old',
        ));

        $update = new UpdateUserUseCase($this->users);
        $updated = $update->execute('u-1', 'New', null);

        $this->assertSame('New', $updated->name);
        $this->assertSame('keep@example.com', $updated->email);
    }

    public function test_update_email_only_preserves_name(): void
    {
        $this->users->save(new User(
            new UserId('u-1'),
            new Email('old@example.com'),
            'Ada',
        ));

        $update = new UpdateUserUseCase($this->users);
        $updated = $update->execute('u-1', null, 'new@example.com');

        $this->assertSame('Ada', $updated->name);
        $this->assertSame('new@example.com', $updated->email);
    }

    public function test_update_with_both_fields_null_throws(): void
    {
        $this->users->save(new User(
            new UserId('u-1'),
            new Email('a@b.com'),
            'Ada',
        ));

        $update = new UpdateUserUseCase($this->users);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one of name or email must be provided.');
        $update->execute('u-1', null, null);
    }

    public function test_update_throws_when_user_missing(): void
    {
        $update = new UpdateUserUseCase($this->users);

        $this->expectException(UserNotFoundException::class);
        $update->execute('missing-user', 'N', null);
    }

    public function test_subscribe_throws_when_user_missing(): void
    {
        $subscribe = new SubscribeUserToWeatherStationUseCase($this->users);

        $this->expectException(UserNotFoundException::class);
        $subscribe->execute('missing', 'st-1');
    }

    public function test_unsubscribe_throws_when_user_missing(): void
    {
        $unsubscribe = new UnsubscribeUserFromWeatherStationUseCase($this->users);

        $this->expectException(UserNotFoundException::class);
        $unsubscribe->execute('missing', 'st-1');
    }

    public function test_subscribe_and_unsubscribe(): void
    {
        $this->users->save(new User(
            new UserId('u-1'),
            new Email('a@b.com'),
            'Ada',
        ));

        $subscribe = new SubscribeUserToWeatherStationUseCase($this->users);
        $afterSub = $subscribe->execute('u-1', 'st-42');
        $this->assertSame(['st-42'], $afterSub->subscribedWeatherStationIds);

        $unsubscribe = new UnsubscribeUserFromWeatherStationUseCase($this->users);
        $afterUnsub = $unsubscribe->execute('u-1', 'st-42');
        $this->assertSame([], $afterUnsub->subscribedWeatherStationIds);
    }

    public function test_delete_user(): void
    {
        $this->users->save(new User(
            new UserId('u-1'),
            new Email('a@b.com'),
            'Ada',
        ));

        $delete = new DeleteUserUseCase($this->users);
        $delete->execute('u-1');

        $get = new GetUserUseCase($this->users);
        $this->expectException(UserNotFoundException::class);
        $get->execute('u-1');
    }
}
