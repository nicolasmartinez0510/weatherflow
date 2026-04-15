<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Tests\TestCase;

final class MeasurementApiTest extends TestCase
{
    public function test_create_and_show_measurement(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'm@example.com',
            'name' => 'M',
        ])->json('id');

        $stationId = $this->postJson('/api/stations', [
            'owner_id' => $ownerId,
            'name' => 'S',
            'latitude' => 0.0,
            'longitude' => 0.0,
            'sensor_model' => 'X',
        ])->json('id');

        $create = $this->postJson('/api/measurements', [
            'station_id' => $stationId,
            'temperature' => 22.5,
            'humidity' => 55.0,
            'pressure' => 1013.2,
            'reported_at' => '2026-04-12T10:00:00+00:00',
        ]);

        $create->assertCreated();
        $id = $create->json('id');
        $this->assertIsString($id);
        $create->assertJsonPath('alert', false);
        $create->assertJsonPath('alert_type', 'Ninguna');

        $show = $this->getJson('/api/measurements/'.$id);
        $show->assertOk();
        $show->assertJsonPath('station_id', $stationId);
        $show->assertJsonPath('temperature', 22.5);
        $show->assertJsonPath('humidity', 55);
        $show->assertJsonPath('pressure', 1013.2);
    }

    public function test_create_sets_alert_for_extreme_heat(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'heat@example.com',
            'name' => 'H',
        ])->json('id');

        $stationId = $this->postJson('/api/stations', [
            'owner_id' => $ownerId,
            'name' => 'Hot',
            'latitude' => 0.0,
            'longitude' => 0.0,
            'sensor_model' => 'X',
        ])->json('id');

        $this->postJson('/api/measurements', [
            'station_id' => $stationId,
            'temperature' => 41.0,
            'humidity' => 50.0,
            'pressure' => 1000.0,
            'reported_at' => '2026-04-12T10:00:00+00:00',
        ])
            ->assertCreated()
            ->assertJsonPath('alert', true)
            ->assertJsonPath('alert_type', 'Calor extremo');
    }

    public function test_create_returns_404_when_station_missing(): void
    {
        $this->postJson('/api/measurements', [
            'station_id' => '00000000-0000-0000-0000-000000000099',
            'temperature' => 20.0,
            'humidity' => 50.0,
            'pressure' => 1000.0,
            'reported_at' => '2026-04-12T10:00:00+00:00',
        ])->assertNotFound();
    }

    public function test_show_returns_404_for_unknown_measurement(): void
    {
        $this->getJson('/api/measurements/unknown-measurement-id')
            ->assertNotFound();
    }

    public function test_list_by_station_returns_measurements_newest_first(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'list@example.com',
            'name' => 'L',
        ])->json('id');

        $stationId = $this->postJson('/api/stations', [
            'owner_id' => $ownerId,
            'name' => 'List',
            'latitude' => 0.0,
            'longitude' => 0.0,
            'sensor_model' => 'X',
        ])->json('id');

        $this->postJson('/api/measurements', [
            'station_id' => $stationId,
            'temperature' => 20.0,
            'humidity' => 50.0,
            'pressure' => 1000.0,
            'reported_at' => '2026-04-12T10:00:00+00:00',
        ])->assertCreated();

        $this->postJson('/api/measurements', [
            'station_id' => $stationId,
            'temperature' => 21.0,
            'humidity' => 50.0,
            'pressure' => 1000.0,
            'reported_at' => '2026-04-13T10:00:00+00:00',
        ])->assertCreated();

        $list = $this->getJson('/api/stations/'.$stationId.'/measurements');
        $list->assertOk();
        $list->assertJsonCount(2);
        $list->assertJsonPath('0.temperature', 21);
        $list->assertJsonPath('1.temperature', 20);
    }

    public function test_list_by_station_returns_404_when_station_unknown(): void
    {
        $this->getJson('/api/stations/unknown-station/measurements')
            ->assertNotFound();
    }

    public function test_patch_measurement_updates_and_re_evaluates_alert(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'patch-m@example.com',
            'name' => 'P',
        ])->json('id');

        $stationId = $this->postJson('/api/stations', [
            'owner_id' => $ownerId,
            'name' => 'P',
            'latitude' => 0.0,
            'longitude' => 0.0,
            'sensor_model' => 'X',
        ])->json('id');

        $id = $this->postJson('/api/measurements', [
            'station_id' => $stationId,
            'temperature' => 20.0,
            'humidity' => 50.0,
            'pressure' => 1000.0,
            'reported_at' => '2026-04-12T10:00:00+00:00',
        ])->json('id');

        $this->patchJson('/api/measurements/'.$id, [
            'temperature' => 41.0,
        ])
            ->assertOk()
            ->assertJsonPath('alert', true)
            ->assertJsonPath('alert_type', 'Calor extremo');
    }

    public function test_delete_measurement_returns_no_content(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'del-m@example.com',
            'name' => 'D',
        ])->json('id');

        $stationId = $this->postJson('/api/stations', [
            'owner_id' => $ownerId,
            'name' => 'D',
            'latitude' => 0.0,
            'longitude' => 0.0,
            'sensor_model' => 'X',
        ])->json('id');

        $id = $this->postJson('/api/measurements', [
            'station_id' => $stationId,
            'temperature' => 20.0,
            'humidity' => 50.0,
            'pressure' => 1000.0,
            'reported_at' => '2026-04-12T10:00:00+00:00',
        ])->json('id');

        $this->deleteJson('/api/measurements/'.$id)->assertNoContent();
    }

    public function test_store_returns_422_when_station_id_missing(): void
    {
        $this->postJson('/api/measurements', [
            'temperature' => 20.0,
            'humidity' => 50.0,
            'pressure' => 1000.0,
            'reported_at' => '2026-04-12T10:00:00+00:00',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['station_id']);
    }

    public function test_store_returns_422_when_humidity_out_of_range(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'hum@example.com',
            'name' => 'H',
        ])->json('id');

        $stationId = $this->postJson('/api/stations', [
            'owner_id' => $ownerId,
            'name' => 'H',
            'latitude' => 0.0,
            'longitude' => 0.0,
            'sensor_model' => 'X',
        ])->json('id');

        $this->postJson('/api/measurements', [
            'station_id' => $stationId,
            'temperature' => 20.0,
            'humidity' => 101.0,
            'pressure' => 1000.0,
            'reported_at' => '2026-04-12T10:00:00+00:00',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['humidity']);
    }

    public function test_patch_returns_422_when_body_empty(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'empty-m@example.com',
            'name' => 'E',
        ])->json('id');

        $stationId = $this->postJson('/api/stations', [
            'owner_id' => $ownerId,
            'name' => 'E',
            'latitude' => 0.0,
            'longitude' => 0.0,
            'sensor_model' => 'X',
        ])->json('id');

        $id = $this->postJson('/api/measurements', [
            'station_id' => $stationId,
            'temperature' => 20.0,
            'humidity' => 50.0,
            'pressure' => 1000.0,
            'reported_at' => '2026-04-12T10:00:00+00:00',
        ])->json('id');

        $this->patchJson('/api/measurements/'.$id, [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['temperature']);
    }

    public function test_get_returns_404_after_measurement_deleted(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'gone-m@example.com',
            'name' => 'G',
        ])->json('id');

        $stationId = $this->postJson('/api/stations', [
            'owner_id' => $ownerId,
            'name' => 'G',
            'latitude' => 0.0,
            'longitude' => 0.0,
            'sensor_model' => 'X',
        ])->json('id');

        $id = $this->postJson('/api/measurements', [
            'station_id' => $stationId,
            'temperature' => 20.0,
            'humidity' => 50.0,
            'pressure' => 1000.0,
            'reported_at' => '2026-04-12T10:00:00+00:00',
        ])->json('id');

        $this->deleteJson('/api/measurements/'.$id)->assertNoContent();

        $this->getJson('/api/measurements/'.$id)->assertNotFound();
    }

    public function test_patch_returns_404_when_measurement_unknown(): void
    {
        $this->patchJson('/api/measurements/unknown-id', [
            'temperature' => 1.0,
        ])->assertNotFound();
    }

    public function test_delete_unknown_measurement_returns_no_content(): void
    {
        $this->deleteJson('/api/measurements/00000000-0000-0000-0000-000000000099')
            ->assertNoContent();
    }

    public function test_history_filters_by_station_name(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'history-name@example.com',
            'name' => 'HN',
        ])->json('id');

        $northStation = $this->postJson('/api/stations', [
            'owner_id' => $ownerId,
            'name' => 'North Base',
            'latitude' => 0.0,
            'longitude' => 0.0,
            'sensor_model' => 'X',
        ])->json('id');

        $southStation = $this->postJson('/api/stations', [
            'owner_id' => $ownerId,
            'name' => 'South Base',
            'latitude' => 1.0,
            'longitude' => 1.0,
            'sensor_model' => 'Y',
        ])->json('id');

        $this->postJson('/api/measurements', [
            'station_id' => $northStation,
            'temperature' => 22.0,
            'humidity' => 50.0,
            'pressure' => 1000.0,
            'reported_at' => '2026-04-12T10:00:00+00:00',
        ])->assertCreated();

        $this->postJson('/api/measurements', [
            'station_id' => $southStation,
            'temperature' => 23.0,
            'humidity' => 50.0,
            'pressure' => 1000.0,
            'reported_at' => '2026-04-13T10:00:00+00:00',
        ])->assertCreated();

        $this->getJson('/api/measurements?station_name=north')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.station_id', $northStation);
    }

    public function test_history_filters_by_temperature_range_and_alerts_only(): void
    {
        $ownerId = $this->postJson('/api/users', [
            'email' => 'history-filters@example.com',
            'name' => 'HF',
        ])->json('id');

        $stationId = $this->postJson('/api/stations', [
            'owner_id' => $ownerId,
            'name' => 'Filter Station',
            'latitude' => 0.0,
            'longitude' => 0.0,
            'sensor_model' => 'X',
        ])->json('id');

        $this->postJson('/api/measurements', [
            'station_id' => $stationId,
            'temperature' => 25.0,
            'humidity' => 50.0,
            'pressure' => 1000.0,
            'reported_at' => '2026-04-12T10:00:00+00:00',
        ])->assertCreated();

        $this->postJson('/api/measurements', [
            'station_id' => $stationId,
            'temperature' => 41.0,
            'humidity' => 50.0,
            'pressure' => 1000.0,
            'reported_at' => '2026-04-13T10:00:00+00:00',
        ])->assertCreated();

        $this->getJson('/api/measurements?min_temperature=30&max_temperature=45&alerts_only=1')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.temperature', 41)
            ->assertJsonPath('0.alert', true);
    }

    public function test_history_returns_422_when_min_temperature_is_greater_than_max_temperature(): void
    {
        $this->getJson('/api/measurements?min_temperature=50&max_temperature=10')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['min_temperature']);
    }
}
