<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Station\StoreWeatherStationRequest;
use App\Http\Requests\Station\UpdateWeatherStationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use InvalidArgumentException;
use WeatherFlow\Application\Exception\StationNotFoundException;
use WeatherFlow\Application\Exception\UserNotFoundException;
use WeatherFlow\Application\UseCase\Station\CreateWeatherStationUseCase;
use WeatherFlow\Application\UseCase\Station\DeleteWeatherStationUseCase;
use WeatherFlow\Application\UseCase\Station\GetWeatherStationUseCase;
use WeatherFlow\Application\UseCase\Station\UpdateWeatherStationUseCase;
use WeatherFlow\Domain\ValueObject\StationStatus;

final class WeatherStationController extends Controller
{
    public function __construct(
        private readonly CreateWeatherStationUseCase $createStation,
        private readonly GetWeatherStationUseCase $getStation,
        private readonly UpdateWeatherStationUseCase $updateStation,
        private readonly DeleteWeatherStationUseCase $deleteStation,
    ) {}

    public function store(StoreWeatherStationRequest $request): JsonResponse
    {
        $statusRaw = (string) ($request->validated('status') ?? StationStatus::Active->value);
        $status = StationStatus::tryFrom($statusRaw) ?? StationStatus::Active;

        try {
            $response = $this->createStation->execute(
                (string) $request->validated('owner_id'),
                (string) $request->validated('name'),
                (float) $request->validated('latitude'),
                (float) $request->validated('longitude'),
                (string) $request->validated('sensor_model'),
                $status,
            );
        } catch (UserNotFoundException) {
            return response()->json(['message' => 'User not found.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($response->toArray(), Response::HTTP_CREATED);
    }

    public function show(string $id): JsonResponse
    {
        try {
            $response = $this->getStation->execute($id);
        } catch (StationNotFoundException) {
            return response()->json(['message' => 'Station not found.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($response->toArray());
    }

    public function update(UpdateWeatherStationRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $status = null;
        if (array_key_exists('status', $data)) {
            $status = StationStatus::from((string) $data['status']);
        }

        try {
            $response = $this->updateStation->execute(
                $id,
                array_key_exists('name', $data) ? (string) $data['name'] : null,
                array_key_exists('latitude', $data) ? (float) $data['latitude'] : null,
                array_key_exists('longitude', $data) ? (float) $data['longitude'] : null,
                array_key_exists('sensor_model', $data) ? (string) $data['sensor_model'] : null,
                $status,
            );
        } catch (StationNotFoundException) {
            return response()->json(['message' => 'Station not found.'], Response::HTTP_NOT_FOUND);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json($response->toArray());
    }

    public function destroy(string $id): Response
    {
        $this->deleteStation->execute($id);

        return response()->noContent();
    }
}
