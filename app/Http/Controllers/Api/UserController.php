<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\SubscriptionRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use WeatherFlow\Application\Exception\StationNotFoundException;
use WeatherFlow\Application\Exception\UserNotFoundException;
use WeatherFlow\Application\UseCase\User\CreateUserUseCase;
use WeatherFlow\Application\UseCase\User\DeleteUserUseCase;
use WeatherFlow\Application\UseCase\User\GetUserUseCase;
use WeatherFlow\Application\UseCase\User\ListUsersUseCase;
use WeatherFlow\Application\UseCase\User\SubscribeUserToWeatherStationUseCase;
use WeatherFlow\Application\UseCase\User\UnsubscribeUserFromWeatherStationUseCase;
use WeatherFlow\Application\UseCase\User\UpdateUserUseCase;

final class UserController extends Controller
{
    public function __construct(
        private readonly CreateUserUseCase $createUser,
        private readonly ListUsersUseCase $listUsers,
        private readonly GetUserUseCase $getUser,
        private readonly UpdateUserUseCase $updateUser,
        private readonly DeleteUserUseCase $deleteUser,
        private readonly SubscribeUserToWeatherStationUseCase $subscribeUser,
        private readonly UnsubscribeUserFromWeatherStationUseCase $unsubscribeUser,
    ) {
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $response = $this->createUser->execute(
            (string) $request->validated('email'),
            (string) $request->validated('name'),
        );

        return response()->json($response->toArray(), Response::HTTP_CREATED);
    }

    public function index(): JsonResponse
    {
        $items = $this->listUsers->execute();

        return response()->json(array_map(
            static fn ($r) => $r->toArray(),
            $items,
        ));
    }

    public function show(string $id): JsonResponse
    {
        try {
            $response = $this->getUser->execute($id);
        } catch (UserNotFoundException) {
            return response()->json(['message' => 'User not found.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($response->toArray());
    }

    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        try {
            $response = $this->updateUser->execute(
                $id,
                $request->filled('name') ? (string) $request->validated('name') : null,
                $request->filled('email') ? (string) $request->validated('email') : null,
            );
        } catch (UserNotFoundException) {
            return response()->json(['message' => 'User not found.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($response->toArray());
    }

    public function destroy(string $id): JsonResponse
    {
        $this->deleteUser->execute($id);

        return response()->json('User deleted successfully.', Response::HTTP_NO_CONTENT);
    }

    public function subscribe(SubscriptionRequest $request, string $id): JsonResponse
    {
        try {
            $response = $this->subscribeUser->execute($id, (string) $request->validated('weather_station_id'));
        } catch (StationNotFoundException) {
            return response()->json(['message' => 'Weather Station not found.'], Response::HTTP_NOT_FOUND);
        } catch (UserNotFoundException) {
            return response()->json(['message' => 'User not found.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($response->toArray());
    }

    public function unsubscribe(string $id, string $weatherStationId): JsonResponse
    {
        try {
            $response = $this->unsubscribeUser->execute($id, $weatherStationId);
        } catch (UserNotFoundException) {
            return response()->json(['message' => 'User not found.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($response->toArray());
    }
}
