<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Tests\TestCase;

final class UserApiTest extends TestCase
{
    public function test_index_returns_empty_list_when_no_users(): void
    {
        $this->getJson('/api/users')
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_index_returns_all_users(): void
    {
        $first = $this->postJson('/api/users', [
            'email' => 'first@example.com',
            'name' => 'First',
        ])->json('id');
        $second = $this->postJson('/api/users', [
            'email' => 'second@example.com',
            'name' => 'Second',
        ])->json('id');

        $response = $this->getJson('/api/users');

        $response->assertOk()->assertJsonCount(2);
        $response->assertJsonFragment([
            'id' => $first,
            'email' => 'first@example.com',
            'name' => 'First',
            'subscribed_weather_station_ids' => [],
        ]);
        $response->assertJsonFragment([
            'id' => $second,
            'email' => 'second@example.com',
            'name' => 'Second',
            'subscribed_weather_station_ids' => [],
        ]);
    }

    public function test_create_and_show_user(): void
    {
        $create = $this->postJson('/api/users', [
            'email' => 'ada@example.com',
            'name' => 'Ada',
        ]);

        $create->assertCreated();
        $id = $create->json('id');
        $this->assertIsString($id);

        $show = $this->getJson('/api/users/'.$id);
        $show->assertOk();
        $show->assertJsonPath('email', 'ada@example.com');
        $show->assertJsonPath('name', 'Ada');
        $show->assertJsonPath('subscribed_weather_station_ids', []);
    }

    public function test_show_returns_404_for_unknown_user(): void
    {
        $this->getJson('/api/users/does-not-exist')
            ->assertNotFound();
    }

    public function test_subscribe_and_unsubscribe(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'bob@example.com',
            'name' => 'Bob',
        ])->json('id');

        $stationId = $this->postJson('/api/weather-stations', [
            'owner_id' => $ownerId,
            'name' => 'Station 1',
            'latitude' => -34.6,
            'longitude' => -58.4,
            'sensor_model' => 'BME280',
            'status' => 'active',
        ])->json('id');

        $this->postJson('/api/users/'.$ownerId.'/subscriptions', [
            'weather_station_id' => $stationId,
        ])->assertOk()->assertJsonPath('subscribed_weather_station_ids', [$stationId]);

        $this->deleteJson('/api/users/'.$ownerId.'/subscriptions/'.$stationId)
            ->assertOk()
            ->assertJsonPath('subscribed_weather_station_ids', []);
    }

    public function test_patch_user(): void
    {
        $id = $this->postJson('/api/users', [
            'email' => 'c@example.com',
            'name' => 'C',
        ])->json('id');

        $this->patchJson('/api/users/'.$id, [
            'name' => 'Cee',
        ])->assertOk()->assertJsonPath('name', 'Cee');

        $this->patchJson('/api/users/'.$id, [
            'email' => 'cee@example.com',
        ])->assertOk()->assertJsonPath('email', 'cee@example.com');
    }

    public function test_delete_user_returns_no_content(): void
    {
        $id = $this->postJson('/api/users', [
            'email' => 'd@example.com',
            'name' => 'D',
        ])->json('id');

        $this->deleteJson('/api/users/'.$id)->assertNoContent();
    }

    public function test_store_returns_422_when_email_invalid(): void
    {
        $this->postJson('/api/users', [
            'email' => 'not-valid',
            'name' => 'Ada',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_returns_422_when_name_missing(): void
    {
        $this->postJson('/api/users', [
            'email' => 'ada@example.com',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_returns_422_when_email_missing(): void
    {
        $this->postJson('/api/users', [
            'name' => 'Ada',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_patch_returns_422_when_body_empty(): void
    {
        $id = $this->postJson('/api/users', [
            'email' => 'patch@example.com',
            'name' => 'P',
        ])->json('id');

        $this->patchJson('/api/users/'.$id, [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_patch_returns_422_when_email_invalid(): void
    {
        $id = $this->postJson('/api/users', [
            'email' => 'ok@example.com',
            'name' => 'P',
        ])->json('id');

        $this->patchJson('/api/users/'.$id, [
            'email' => 'bad',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_subscribe_returns_422_without_weather_station_id(): void
    {
        $id = $this->postJson('/api/users', [
            'email' => 'sub@example.com',
            'name' => 'S',
        ])->json('id');

        $this->postJson('/api/users/'.$id.'/subscriptions', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['weather_station_id']);
    }

    public function test_patch_returns_404_when_user_does_not_exist(): void
    {
        $this->patchJson('/api/users/unknown-user-id', [
            'name' => 'N',
        ])->assertNotFound();
    }

    public function test_subscribe_returns_404_when_user_does_not_exist(): void
    {
        $this->postJson('/api/users/unknown-user-id/subscriptions', [
            'weather_station_id' => 'station-id',
        ])->assertNotFound();
    }

    public function test_subscribe_returns_404_when_station_does_not_exist(): void
    {
        $id = $this->postJson('/api/users', [
            'email' => 'station404@example.com',
            'name' => 'Station404',
        ])->json('id');

        $this->postJson('/api/users/'.$id.'/subscriptions', [
            'weather_station_id' => 'missing-station',
        ])->assertNotFound();
    }

    public function test_unsubscribe_returns_404_when_user_does_not_exist(): void
    {
        $this->deleteJson('/api/users/unknown-user-id/subscriptions/st-1')
            ->assertNotFound();
    }

    public function test_get_returns_404_after_user_deleted(): void
    {
        $id = $this->postJson('/api/users', [
            'email' => 'gone@example.com',
            'name' => 'G',
        ])->json('id');

        $this->deleteJson('/api/users/'.$id)->assertNoContent();

        $this->getJson('/api/users/'.$id)->assertNotFound();
    }

    public function test_delete_unknown_user_returns_no_content(): void
    {
        $this->deleteJson('/api/users/00000000-0000-0000-0000-000000000088')
            ->assertNoContent();
    }
}
