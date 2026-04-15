<?php

declare(strict_types=1);

namespace WeatherFlow\Infrastructure\Persistence\Mongo;

use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeInterface;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Model\BSONDocument;
use WeatherFlow\Domain\Entity\Measurement;
use WeatherFlow\Domain\Entity\WeatherflowEntity;
use WeatherFlow\Domain\Repository\MeasurementRepository;
use WeatherFlow\Domain\ValueObject\Humidity;
use WeatherFlow\Domain\ValueObject\MeasurementId;
use WeatherFlow\Domain\ValueObject\StationId;

/**
 * @extends MongoPersistence<Measurement, MeasurementId>
 */
final class MongoMeasurementRepository extends MongoPersistence
    implements MeasurementRepository
{
    private Collection $stations;

    public function __construct(Client $client, string $databaseName, string $collectionName = 'measurements') {
        parent::__construct($client, $databaseName, $collectionName);
        $this->stations = $this->database->selectCollection('stations');

        $this->collection->createIndex(['stationId' => 1, 'reportedAt' => -1]);
        $this->collection->createIndex(['temperatureCelsius' => 1, 'reportedAt' => -1]);
        $this->collection->createIndex(['alert' => 1, 'reportedAt' => -1]);

        $this->stations->createIndex(['name' => 1]);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function findByStationId(StationId $stationId): array {
        $mesurementDocs = $this->collection->find(
            ['stationId' => $stationId->value],
            [
                'sort' => ['reportedAt' => -1],
            ],
        );

        return $this->mapDocumentsToMesurements($mesurementDocs);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function findHistory(
        ?string $stationName,
        ?float $minTemperature,
        ?float $maxTemperature,
        bool $alertsOnly,
    ): array {
        $pipeline = [];

        $match = [];
        if ($alertsOnly) {
            $match['alert'] = true;
        }
        if ($minTemperature !== null || $maxTemperature !== null) {
            $match['temperatureCelsius'] = [];
            if ($minTemperature !== null) {
                $match['temperatureCelsius']['$gte'] = $minTemperature;
            }
            if ($maxTemperature !== null) {
                $match['temperatureCelsius']['$lte'] = $maxTemperature;
            }
        }
        if ($match !== []) {
            $pipeline[] = ['$match' => $match];
        }

        if ($stationName !== null && trim($stationName) !== '') {
            $pipeline[] = [
                '$lookup' => [
                    'from' => 'stations',
                    'localField' => 'stationId',
                    'foreignField' => '_id',
                    'as' => 'station',
                ],
            ];
            $pipeline[] = ['$unwind' => '$station'];
            $pipeline[] = [
                '$match' => [
                    'station.name' => [
                        '$regex' => preg_quote(trim($stationName), '/'),
                        '$options' => 'i',
                    ],
                ],
            ];
        }

        $pipeline[] = ['$sort' => ['reportedAt' => -1]];

        $mesurementDocs = $this->collection->aggregate($pipeline);

        return $this->mapDocumentsToMesurements($mesurementDocs);
    }

    protected function getDocByEntity(Measurement|WeatherflowEntity $entity): array|object {
        return [
            '_id' => $entity->id()->value,
            'stationId' => $entity->stationId()->value,
            'temperatureCelsius' => $entity->temperatureCelsius(),
            'humidityPercent' => $entity->humidity()->percent,
            'pressureHpa' => $entity->pressureHpa(),
            'reportedAt' => $entity->reportedAt()->format(DateTimeInterface::ATOM),
            'alert' => $entity->alert(),
            'alertType' => $entity->alertType(),
        ];
    }

    /**
     * @throws DateMalformedStringException
     */
    private function mapDocumentsToMesurements($mesurementDocs): array {
        $mesurements = [];
        foreach ($mesurementDocs as $doc) {
            $mesurements[] = $this->mapDocumentToEntity($doc);
        }
        return $mesurements;
    }

    /**
     * @param BSONDocument|array<string, mixed> $doc
     * @throws DateMalformedStringException
     */
    protected function mapDocumentToEntity(array|object $doc): Measurement {
        $data = $this->documentToArray($doc);

        $reportedRaw = (string) ($data['reportedAt'] ?? '');
        $reported = DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $reportedRaw)
            ?: new DateTimeImmutable($reportedRaw);

        return new Measurement(
            new MeasurementId((string) $data['_id']),
            new StationId((string) $data['stationId']),
            (float) $data['temperatureCelsius'],
            new Humidity((float) $data['humidityPercent']),
            (float) $data['pressureHpa'],
            $reported,
            (bool) ($data['alert'] ?? false),
            (string) ($data['alertType'] ?? 'Ninguna'),
        );
    }

    /**
     * @param  BSONDocument|array<string, mixed>  $doc
     * @return array<string, mixed>
     */
    private function documentToArray(array|object $doc): array {
        if (is_array($doc)) {
            return $doc;
        }
        if ($doc instanceof BSONDocument) {
            return $doc->getArrayCopy();
        }

        return iterator_to_array($doc);
    }
}
