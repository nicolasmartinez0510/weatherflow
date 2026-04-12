<?php

declare(strict_types=1);

namespace WeatherFlow\Infrastructure\Persistence\Mongo;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Model\BSONDocument;
use WeatherFlow\Domain\Entity\WeatherStation;
use WeatherFlow\Domain\Repository\WeatherStationRepository;
use WeatherFlow\Domain\ValueObject\Coordinates;
use WeatherFlow\Domain\ValueObject\StationId;
use WeatherFlow\Domain\ValueObject\StationStatus;
use WeatherFlow\Domain\ValueObject\UserId;

final class MongoWeatherStationRepository implements WeatherStationRepository
{
    private Collection $collection;

    public function __construct(Client $client, string $databaseName, string $collectionName = 'stations')
    {
        $this->collection = $client->selectDatabase($databaseName)->selectCollection($collectionName);
    }

    public function save(WeatherStation $station): void
    {
        $coords = $station->coordinates();
        $doc = [
            '_id' => $station->id()->value,
            'name' => $station->name(),
            'latitude' => $coords->latitude,
            'longitude' => $coords->longitude,
            'sensorModel' => $station->sensorModel(),
            'status' => $station->status()->value,
            'ownerId' => $station->ownerId()->value,
        ];

        $this->collection->replaceOne(
            ['_id' => $station->id()->value],
            $doc,
            ['upsert' => true],
        );
    }

    public function findById(StationId $id): ?WeatherStation
    {
        $doc = $this->collection->findOne(['_id' => $id->value]);
        if ($doc === null) {
            return null;
        }

        return $this->mapDocumentToStation($doc);
    }

    public function delete(StationId $id): void
    {
        $this->collection->deleteOne(['_id' => $id->value]);
    }

    // PRIVATE FUNCTIONS

    /**
     * @param  BSONDocument|array<string, mixed>  $doc
     */
    private function mapDocumentToStation(array|object $doc): WeatherStation
    {
        $data = $this->documentToArray($doc);

        $statusRaw = (string) ($data['status'] ?? 'active');
        $status = StationStatus::tryFrom($statusRaw) ?? StationStatus::Active;

        return new WeatherStation(
            new StationId((string) $data['_id']),
            (string) $data['name'],
            new Coordinates((float) $data['latitude'], (float) $data['longitude']),
            (string) $data['sensorModel'],
            $status,
            new UserId((string) $data['ownerId']),
        );
    }

    /**
     * @param  BSONDocument|array<string, mixed>  $doc
     * @return array<string, mixed>
     */
    private function documentToArray(array|object $doc): array
    {
        if (is_array($doc)) {
            return $doc;
        }
        if ($doc instanceof BSONDocument) {
            return $doc->getArrayCopy();
        }

        return iterator_to_array($doc);
    }
}
