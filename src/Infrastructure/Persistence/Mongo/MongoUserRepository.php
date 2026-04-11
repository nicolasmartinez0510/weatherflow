<?php

declare(strict_types=1);

namespace WeatherFlow\Infrastructure\Persistence\Mongo;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Model\BSONDocument;
use WeatherFlow\Domain\Entity\User;
use WeatherFlow\Domain\Repository\UserRepository;
use WeatherFlow\Domain\ValueObject\Email;
use WeatherFlow\Domain\ValueObject\StationId;
use WeatherFlow\Domain\ValueObject\UserId;

final class MongoUserRepository implements UserRepository
{
    private Collection $collection;

    public function __construct(Client $client, string $databaseName, string $collectionName = 'users')
    {
        $this->collection = $client->selectDatabase($databaseName)->selectCollection($collectionName);
    }

    public function save(User $user): void
    {
        $doc = [
            '_id' => $user->id()->value,
            'email' => $user->email()->value,
            'name' => $user->name(),
            'subscribedStationIds' => array_map(
                static fn (StationId $id): string => $id->value,
                $user->subscribedStationIds(),
            ),
        ];

        $this->collection->replaceOne(
            ['_id' => $user->id()->value],
            $doc,
            ['upsert' => true],
        );
    }

    public function findById(UserId $id): ?User
    {
        $doc = $this->collection->findOne(['_id' => $id->value]);
        if ($doc === null) {
            return null;
        }

        return $this->mapDocumentToUser($doc);
    }

    public function delete(UserId $id): void
    {
        $this->collection->deleteOne(['_id' => $id->value]);
    }

    /**
     * @param  BSONDocument|array<string, mixed>  $doc
     */
    private function mapDocumentToUser(array|object $doc): User
    {
        $data = $this->documentToArray($doc);

        $stationRaw = $data['subscribedStationIds'] ?? [];
        $stationList = match (true) {
            is_array($stationRaw) => $stationRaw,
            $stationRaw instanceof \Traversable => iterator_to_array($stationRaw, false),
            default => [],
        };
        $stationIds = array_map(
            static fn (mixed $sid): StationId => new StationId((string) $sid),
            $stationList,
        );

        return new User(
            new UserId((string) $data['_id']),
            new Email((string) $data['email']),
            (string) $data['name'],
            $stationIds,
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
