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
use WeatherFlow\Domain\Repository\MeasurementRepository;
use WeatherFlow\Domain\ValueObject\Humidity;
use WeatherFlow\Domain\ValueObject\MeasurementId;
use WeatherFlow\Domain\ValueObject\StationId;

final class MongoMeasurementRepository implements MeasurementRepository
{
    private Collection $collection;

    public function __construct(Client $client, string $databaseName, string $collectionName = 'measurements') {
        $this->collection = $client->selectDatabase($databaseName)->selectCollection($collectionName);
    }

    public function save(Measurement $measurement): void {
        $doc = [
            '_id' => $measurement->id()->value,
            'stationId' => $measurement->stationId()->value,
            'temperatureCelsius' => $measurement->temperatureCelsius(),
            'humidityPercent' => $measurement->humidity()->percent,
            'pressureHpa' => $measurement->pressureHpa(),
            'reportedAt' => $measurement->reportedAt()->format(DateTimeInterface::ATOM),
            'alert' => $measurement->alert(),
            'alertType' => $measurement->alertType(),
        ];

        $this->collection->replaceOne(
            ['_id' => $measurement->id()->value],
            $doc,
            ['upsert' => true],
        );
    }

    /**
     * @throws DateMalformedStringException
     */
    public function findById(MeasurementId $id): ?Measurement {
        $doc = $this->collection->findOne(['_id' => $id->value]);
        if ($doc === null) {
            return null;
        }

        return $this->mapDocumentToMeasurement($doc);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function findByStationId(StationId $stationId): array {
        $cursor = $this->collection->find(
            ['stationId' => $stationId->value],
            [
                'sort' => ['reportedAt' => -1],
            ],
        );

        $out = [];
        foreach ($cursor as $doc) {
            $out[] = $this->mapDocumentToMeasurement($doc);
        }

        return $out;
    }

    public function delete(MeasurementId $id): void {
        $this->collection->deleteOne(['_id' => $id->value]);
    }

    // PRIVATE FUNCTIONS

    /**
     * @param BSONDocument|array<string, mixed> $doc
     * @throws DateMalformedStringException
     */
    private function mapDocumentToMeasurement(array|object $doc): Measurement {
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
