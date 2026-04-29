<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Tests\TestCase;

final class WeatherStationApiTest extends TestCase
{
    public function test_index_returns_empty_list_when_no_weather_stations(): void
    {
        $this->getJson('/api/weather-stations')
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_index_returns_all_weather_stations(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'stations-index-owner@example.com',
            'name' => 'Owner',
        ])->json('id');

        $first = $this->postJson('/api/weather-stations', [
            'owner_id' => $ownerId,
            'name' => 'Station One',
            'latitude' => -34.5,
            'longitude' => -58.4,
            'sensor_model' => 'S1',
        ])->json('id');
        $second = $this->postJson('/api/weather-stations', [
            'owner_id' => $ownerId,
            'name' => 'Station Two',
            'latitude' => -33.0,
            'longitude' => -57.0,
            'sensor_model' => 'S2',
            'status' => 'inactive',
        ])->json('id');

        $response = $this->getJson('/api/weather-stations');

        $response->assertOk()->assertJsonCount(2);
        $response->assertJsonFragment([
            'id' => $first,
            'name' => 'Station One',
            'latitude' => -34.5,
            'longitude' => -58.4,
            'sensor_model' => 'S1',
            'status' => 'active',
            'owner_id' => $ownerId,
        ]);
        $response->assertJsonFragment([
            'id' => $second,
            'name' => 'Station Two',
            'latitude' => -33.0,
            'longitude' => -57.0,
            'sensor_model' => 'S2',
            'status' => 'inactive',
            'owner_id' => $ownerId,
        ]);
    }

    public function test_create_and_show_weather_station(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'owner@example.com',
            'name' => 'Owner',
        ])->json('id');

        $create = $this->postJson('/api/weather-stations', [
            'owner_id' => $ownerId,
            'name' => 'Estación Central',
            'latitude' => -34.6,
            'longitude' => -58.4,
            'sensor_model' => 'DHT22',
        ]);

        $create->assertCreated();
        $id = $create->json('id');
        $this->assertIsString($id);

        $show = $this->getJson('/api/weather-stations/'.$id);
        $show->assertOk();
        $show->assertJsonPath('name', 'Estación Central');
        $show->assertJsonPath('latitude', -34.6);
        $show->assertJsonPath('longitude', -58.4);
        $show->assertJsonPath('sensor_model', 'DHT22');
        $show->assertJsonPath('status', 'active');
        $show->assertJsonPath('owner_id', $ownerId);
    }

    public function test_create_returns_404_when_owner_missing(): void
    {
        $this->postJson('/api/weather-stations', [
            'owner_id' => '00000000-0000-0000-0000-000000000000',
            'name' => 'Orphan',
            'latitude' => 0.0,
            'longitude' => 0.0,
            'sensor_model' => 'X',
        ])->assertNotFound();
    }

    public function test_show_returns_404_for_unknown_weather_station(): void
    {
        $this->getJson('/api/weather-stations/does-not-exist')
            ->assertNotFound();
    }

    public function test_patch_weather_station(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'p@example.com',
            'name' => 'P',
        ])->json('id');

        $id = $this->postJson('/api/weather-stations', [
            'owner_id' => $ownerId,
            'name' => 'Old',
            'latitude' => 1.0,
            'longitude' => 2.0,
            'sensor_model' => 'A',
            'status' => 'active',
        ])->json('id');

        $this->patchJson('/api/weather-stations/'.$id, [
            'name' => 'New',
        ])->assertOk()->assertJsonPath('name', 'New');

        $this->patchJson('/api/weather-stations/'.$id, [
            'latitude' => 0.0,
            'longitude' => 0.0,
            'status' => 'inactive',
        ])->assertOk()
            ->assertJsonPath('latitude', 0)
            ->assertJsonPath('longitude', 0)
            ->assertJsonPath('status', 'inactive');
    }

    public function test_delete_weather_station_returns_no_content(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'd@example.com',
            'name' => 'D',
        ])->json('id');

        $id = $this->postJson('/api/weather-stations', [
            'owner_id' => $ownerId,
            'name' => 'Gone',
            'latitude' => 0.0,
            'longitude' => 0.0,
            'sensor_model' => 'A',
        ])->json('id');

        $this->deleteJson('/api/weather-stations/'.$id)->assertNoContent();
    }

    public function test_store_returns_422_when_name_missing(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'v@example.com',
            'name' => 'V',
        ])->json('id');

        $this->postJson('/api/weather-stations', [
            'owner_id' => $ownerId,
            'latitude' => 0.0,
            'longitude' => 0.0,
            'sensor_model' => 'A',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_patch_returns_422_when_body_empty(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'patch@example.com',
            'name' => 'P',
        ])->json('id');

        $id = $this->postJson('/api/weather-stations', [
            'owner_id' => $ownerId,
            'name' => 'S',
            'latitude' => 1.0,
            'longitude' => 1.0,
            'sensor_model' => 'A',
        ])->json('id');

        $this->patchJson('/api/weather-stations/'.$id, [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_get_returns_404_after_weather_station_deleted(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'gone@example.com',
            'name' => 'G',
        ])->json('id');

        $id = $this->postJson('/api/weather-stations', [
            'owner_id' => $ownerId,
            'name' => 'X',
            'latitude' => 0.0,
            'longitude' => 0.0,
            'sensor_model' => 'A',
        ])->json('id');

        $this->deleteJson('/api/weather-stations/'.$id)->assertNoContent();

        $this->getJson('/api/weather-stations/'.$id)->assertNotFound();
    }

    public function test_patch_returns_404_when_weather_station_does_not_exist(): void
    {
        $this->patchJson('/api/weather-stations/unknown-station-id', [
            'name' => 'N',
        ])->assertNotFound();
    }

    public function test_store_returns_201_with_explicit_inactive_status(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'inactive-owner@example.com',
            'name' => 'IO',
        ])->json('id');

        $create = $this->postJson('/api/weather-stations', [
            'owner_id' => $ownerId,
            'name' => 'Off',
            'latitude' => 10.0,
            'longitude' => 20.0,
            'sensor_model' => 'X',
            'status' => 'inactive',
        ]);

        $create->assertCreated();
        $this->getJson('/api/weather-stations/'.$create->json('id'))
            ->assertOk()
            ->assertJsonPath('status', 'inactive');
    }

    public function test_store_returns_422_when_owner_id_missing(): void
    {
        $this->postJson('/api/weather-stations', [
            'name' => 'N',
            'latitude' => 0.0,
            'longitude' => 0.0,
            'sensor_model' => 'A',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['owner_id']);
    }

    public function test_store_returns_422_when_latitude_missing(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'lat-miss@example.com',
            'name' => 'L',
        ])->json('id');

        $this->postJson('/api/weather-stations', [
            'owner_id' => $ownerId,
            'name' => 'N',
            'longitude' => 0.0,
            'sensor_model' => 'A',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['latitude']);
    }

    public function test_store_returns_422_when_sensor_model_missing(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'sensor-miss@example.com',
            'name' => 'S',
        ])->json('id');

        $this->postJson('/api/weather-stations', [
            'owner_id' => $ownerId,
            'name' => 'N',
            'latitude' => 0.0,
            'longitude' => 0.0,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sensor_model']);
    }

    public function test_store_returns_422_when_latitude_out_of_range(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'range@example.com',
            'name' => 'R',
        ])->json('id');

        $this->postJson('/api/weather-stations', [
            'owner_id' => $ownerId,
            'name' => 'N',
            'latitude' => 91.0,
            'longitude' => 0.0,
            'sensor_model' => 'A',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['latitude']);
    }

    public function test_store_returns_422_when_longitude_out_of_range(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'lon-range@example.com',
            'name' => 'L',
        ])->json('id');

        $this->postJson('/api/weather-stations', [
            'owner_id' => $ownerId,
            'name' => 'N',
            'latitude' => 0.0,
            'longitude' => 181.0,
            'sensor_model' => 'A',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['longitude']);
    }

    public function test_store_returns_422_when_status_invalid(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'st-invalid@example.com',
            'name' => 'I',
        ])->json('id');

        $this->postJson('/api/weather-stations', [
            'owner_id' => $ownerId,
            'name' => 'N',
            'latitude' => 0.0,
            'longitude' => 0.0,
            'sensor_model' => 'A',
            'status' => 'maybe',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    public function test_patch_returns_422_when_only_latitude_without_longitude(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'lat-only@example.com',
            'name' => 'L',
        ])->json('id');

        $id = $this->postJson('/api/weather-stations', [
            'owner_id' => $ownerId,
            'name' => 'S',
            'latitude' => 1.0,
            'longitude' => 1.0,
            'sensor_model' => 'A',
        ])->json('id');

        $this->patchJson('/api/weather-stations/'.$id, [
            'latitude' => 2.0,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['longitude']);
    }

    public function test_patch_returns_422_when_only_longitude_without_latitude(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'lon-only@example.com',
            'name' => 'L',
        ])->json('id');

        $id = $this->postJson('/api/weather-stations', [
            'owner_id' => $ownerId,
            'name' => 'S',
            'latitude' => 1.0,
            'longitude' => 1.0,
            'sensor_model' => 'A',
        ])->json('id');

        $this->patchJson('/api/weather-stations/'.$id, [
            'longitude' => 3.0,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['latitude']);
    }

    public function test_patch_updates_only_sensor_model(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'sensor-only@example.com',
            'name' => 'SO',
        ])->json('id');

        $id = $this->postJson('/api/weather-stations', [
            'owner_id' => $ownerId,
            'name' => 'Keep',
            'latitude' => 5.0,
            'longitude' => 6.0,
            'sensor_model' => 'OldModel',
        ])->json('id');

        $this->patchJson('/api/weather-stations/'.$id, [
            'sensor_model' => 'NewModel',
        ])
            ->assertOk()
            ->assertJsonPath('sensor_model', 'NewModel')
            ->assertJsonPath('name', 'Keep')
            ->assertJsonPath('latitude', 5)
            ->assertJsonPath('longitude', 6);
    }

    public function test_patch_updates_only_status(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'status-only@example.com',
            'name' => 'ST',
        ])->json('id');

        $id = $this->postJson('/api/weather-stations', [
            'owner_id' => $ownerId,
            'name' => 'S',
            'latitude' => 1.0,
            'longitude' => 2.0,
            'sensor_model' => 'A',
            'status' => 'active',
        ])->json('id');

        $this->patchJson('/api/weather-stations/'.$id, [
            'status' => 'inactive',
        ])
            ->assertOk()
            ->assertJsonPath('status', 'inactive')
            ->assertJsonPath('name', 'S');
    }

    public function test_patch_returns_422_when_status_invalid(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'patch-st@example.com',
            'name' => 'P',
        ])->json('id');

        $id = $this->postJson('/api/weather-stations', [
            'owner_id' => $ownerId,
            'name' => 'S',
            'latitude' => 1.0,
            'longitude' => 2.0,
            'sensor_model' => 'A',
        ])->json('id');

        $this->patchJson('/api/weather-stations/'.$id, [
            'status' => 'unknown',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    public function test_delete_unknown_weather_station_returns_no_content(): void
    {
        $this->deleteJson('/api/weather-stations/00000000-0000-0000-0000-000000000099')
            ->assertNoContent();
    }
}
