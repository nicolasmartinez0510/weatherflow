<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Measurement\ListMeasurementHistoryRequest;
use App\Http\Requests\Measurement\StoreMeasurementRequest;
use App\Http\Requests\Measurement\UpdateMeasurementRequest;
use DateMalformedStringException;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use InvalidArgumentException;
use WeatherFlow\Application\Exception\MeasurementNotFoundException;
use WeatherFlow\Application\Exception\StationNotFoundException;
use WeatherFlow\Application\UseCase\Measurement\CreateMeasurementUseCase;
use WeatherFlow\Application\UseCase\Measurement\DeleteMeasurementUseCase;
use WeatherFlow\Application\UseCase\Measurement\GetMeasurementUseCase;
use WeatherFlow\Application\UseCase\Measurement\ListMeasurementHistoryUseCase;
use WeatherFlow\Application\UseCase\Measurement\ListMeasurementsByStationUseCase;
use WeatherFlow\Application\UseCase\Measurement\UpdateMeasurementUseCase;

final class MeasurementController extends Controller
{
    public function __construct(
        private readonly CreateMeasurementUseCase $createMeasurement,
        private readonly GetMeasurementUseCase $getMeasurement,
        private readonly UpdateMeasurementUseCase $updateMeasurement,
        private readonly DeleteMeasurementUseCase $deleteMeasurement,
        private readonly ListMeasurementHistoryUseCase $listHistory,
        private readonly ListMeasurementsByStationUseCase $listByStation,
    ) {}

    /**
     * @throws DateMalformedStringException
     */
    public function store(StoreMeasurementRequest $request): JsonResponse
    {
        $data = $request->validated();
        $reportedAt = new DateTimeImmutable((string) $data['reported_at']);

        try {
            $response = $this->createMeasurement->execute(
                (string) $data['station_id'],
                (float) $data['temperature'],
                (float) $data['humidity'],
                (float) $data['pressure'],
                $reportedAt,
            );
        } catch (StationNotFoundException) {
            return response()->json(['message' => 'Station not found.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($response->toArray(), Response::HTTP_CREATED);
    }

    public function show(string $id): JsonResponse
    {
        try {
            $response = $this->getMeasurement->execute($id);
        } catch (MeasurementNotFoundException) {
            return response()->json(['message' => 'Measurement not found.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($response->toArray());
    }

    public function index(ListMeasurementHistoryRequest $request): JsonResponse
    {
        $items = $this->listHistory->execute(
            $request->has('station_name') ? (string) $request->input('station_name') : null,
            $request->has('min_temperature') ? (float) $request->input('min_temperature') : null,
            $request->has('max_temperature') ? (float) $request->input('max_temperature') : null,
            $request->has('alerts_only')
                ? filter_var($request->input('alerts_only'), FILTER_VALIDATE_BOOLEAN)
                : false,
        );

        return response()->json(array_map(
            static fn ($r) => $r->toArray(),
            $items,
        ));
    }

    /**
     * @throws DateMalformedStringException
     */
    public function update(UpdateMeasurementRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $reportedAt = null;
        if (array_key_exists('reported_at', $data)) {
            $reportedAt = new DateTimeImmutable((string) $data['reported_at']);
        }

        try {
            $response = $this->updateMeasurement->execute(
                $id,
                array_key_exists('temperature', $data) ? (float) $data['temperature'] : null,
                array_key_exists('humidity', $data) ? (float) $data['humidity'] : null,
                array_key_exists('pressure', $data) ? (float) $data['pressure'] : null,
                $reportedAt,
            );
        } catch (MeasurementNotFoundException) {
            return response()->json(['message' => 'Measurement not found.'], Response::HTTP_NOT_FOUND);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json($response->toArray());
    }

    public function destroy(string $id): Response
    {
        $this->deleteMeasurement->execute($id);

        return response()->noContent();
    }

    public function indexByStation(string $stationId): JsonResponse
    {
        try {
            $items = $this->listByStation->execute($stationId);
        } catch (StationNotFoundException) {
            return response()->json(['message' => 'Station not found.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(array_map(
            static fn ($r) => $r->toArray(),
            $items,
        ));
    }
}
