<?php

declare(strict_types=1);

namespace WeatherFlow\Infrastructure\Persistence\Mongo;

use MongoDB\Client;
use MongoDB\Model\BSONDocument;
use WeatherFlow\Domain\Entity\WeatherflowEntity;
use WeatherFlow\Domain\Entity\WeatherStation;
use WeatherFlow\Domain\Repository\WeatherStationRepository;
use WeatherFlow\Domain\ValueObject\Coordinates;
use WeatherFlow\Domain\ValueObject\WeatherStationStatus;
use WeatherFlow\Domain\ValueObject\UserId;
use WeatherFlow\Domain\ValueObject\WeatherStationId;

/**
 * @extends MongoPersistence<WeatherStation, WeatherStationId>
 */
final class MongoWeatherStationRepository extends MongoPersistence implements WeatherStationRepository
{
    public function __construct(Client $client, string $databaseName, string $collectionName = 'stations')
    {
        parent::__construct($client, $databaseName, $collectionName);
    }

    protected function getDocByEntity(WeatherStation|WeatherflowEntity $entity): array|object
    {
        $coords = $entity->coordinates();

        return [
            '_id' => $entity->id()->value,
            'name' => $entity->name(),
            'latitude' => $coords->latitude,
            'longitude' => $coords->longitude,
            'sensorModel' => $entity->sensorModel(),
            'status' => $entity->status()->value,
            'ownerId' => $entity->ownerId()->value,
        ];
    }

    /**
     * @param  BSONDocument|array<string, mixed>  $doc
     */
    protected function mapDocumentToEntity(array|object $doc): WeatherStation
    {
        $data = $this->documentToArray($doc);

        $statusRaw = (string) ($data['status'] ?? 'active');
        $status = WeatherStationStatus::tryFrom($statusRaw) ?? WeatherStationStatus::Active;

        return new WeatherStation(
            new WeatherStationId((string) $data['_id']),
            (string) $data['name'],
            new Coordinates((float) $data['latitude'], (float) $data['longitude']),
            (string) $data['sensorModel'],
            $status,
            new UserId((string) $data['ownerId']),
        );
    }

    /**
     * @return list<WeatherStation>
     */
    public function findAll(): array
    {
        $docs = $this->collection->find([], ['sort' => ['name' => 1, '_id' => 1]]);
        $stations = [];

        foreach ($docs as $doc) {
            $stations[] = $this->mapDocumentToEntity($doc);
        }

        return $stations;
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
