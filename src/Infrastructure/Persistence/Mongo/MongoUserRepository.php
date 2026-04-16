<?php

declare(strict_types=1);

namespace WeatherFlow\Infrastructure\Persistence\Mongo;

use MongoDB\Client;
use MongoDB\Model\BSONDocument;
use Traversable;
use WeatherFlow\Domain\Entity\User;
use WeatherFlow\Domain\Entity\WeatherflowEntity;
use WeatherFlow\Domain\Repository\UserRepository;
use WeatherFlow\Domain\ValueObject\Email;
use WeatherFlow\Domain\ValueObject\UserId;
use WeatherFlow\Domain\ValueObject\WeatherStationId;

/**
 * @extends MongoPersistence<User, UserId>
 */
final class MongoUserRepository extends MongoPersistence implements UserRepository
{
    public function __construct(Client $client, string $databaseName, string $collectionName = 'users')
    {
        parent::__construct($client, $databaseName, $collectionName);
    }

    protected function getDocByEntity(User|WeatherflowEntity $entity): array|object
    {
        return [
            '_id' => $entity->id()->value,
            'email' => $entity->email()->value,
            'name' => $entity->name(),
            'subscribedWeatherStationIds' => array_map(
                static fn (WeatherStationId $id): string => $id->value,
                $entity->subscribedWeatherStationIds(),
            ),
        ];
    }

    /**
     * @param  BSONDocument|array<string, mixed>  $doc
     */
    protected function mapDocumentToEntity(array|object $doc): User
    {
        $data = $this->documentToArray($doc);

        $stationRaw = $data['subscribedWeatherStationIds'] ?? $data['subscribedStationIds'] ?? [];
        $stationList = match (true) {
            is_array($stationRaw) => $stationRaw,
            $stationRaw instanceof Traversable => iterator_to_array($stationRaw, false),
            default => [],
        };
        $weatherStationIds = array_map(
            static fn (mixed $sid): WeatherStationId => new WeatherStationId((string) $sid),
            $stationList,
        );

        return new User(
            new UserId((string) $data['_id']),
            new Email((string) $data['email']),
            (string) $data['name'],
            $weatherStationIds,
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
