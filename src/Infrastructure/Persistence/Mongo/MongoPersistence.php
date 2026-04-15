<?php

namespace WeatherFlow\Infrastructure\Persistence\Mongo;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;
use WeatherFlow\Domain\Entity\WeatherflowEntity;
use WeatherFlow\Domain\ValueObject\Id;

abstract class MongoPersistence {

    protected Collection $collection;
    protected Database $database;

    public function __construct(Client $client, string $databaseName, string $collectionName) {
        $this->database = $client->selectDatabase($databaseName);
        $this->collection = $this->database->selectCollection($collectionName);
    }

    /**
     * @param array<string, mixed>|object $doc
     * @return WeatherflowEntity
     */
    abstract protected function mapDocumentToEntity(array|object $doc): WeatherflowEntity;

    /**
     * @param WeatherflowEntity $entity
     * @return array|object
     */
    abstract protected function getDocByEntity(WeatherflowEntity $entity): array|object;

    /**
     * @param WeatherflowEntity $entity
     * @return void
     */
    public function save(WeatherflowEntity $entity): void {
        $doc = $this->getDocByEntity($entity);

        $this->collection->replaceOne(
            ['_id' => $entity->id()->value],
            $doc,
            ['upsert' => true],
        );
    }

    /**
     * @param Id $id
     * @return WeatherflowEntity|null
     */
    public function findById(Id $id): ?WeatherflowEntity {
        $doc = $this->collection->findOne(['_id' => $id->value]);
        if ($doc === null) {
            return null;
        }
        return $this->mapDocumentToEntity($doc);
    }

    /**
     * @param Id $id
     * @return void
     */
    public function delete(Id $id): void {
        $this->collection->deleteOne(['_id' => $id->value]);
    }

}
