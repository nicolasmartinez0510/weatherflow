<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\SubscriptionRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use WeatherFlow\Application\Exception\UserNotFoundException;
use WeatherFlow\Application\UseCase\User\CreateUserUseCase;
use WeatherFlow\Application\UseCase\User\DeleteUserUseCase;
use WeatherFlow\Application\UseCase\User\GetUserUseCase;
use WeatherFlow\Application\UseCase\User\SubscribeUserToStationUseCase;
use WeatherFlow\Application\UseCase\User\UnsubscribeUserFromStationUseCase;
use WeatherFlow\Application\UseCase\User\UpdateUserUseCase;

final class UserController extends Controller
{
    public function __construct(
        private readonly CreateUserUseCase $createUser,
        private readonly GetUserUseCase $getUser,
        private readonly UpdateUserUseCase $updateUser,
        private readonly DeleteUserUseCase $deleteUser,
        private readonly SubscribeUserToStationUseCase $subscribeUser,
        private readonly UnsubscribeUserFromStationUseCase $unsubscribeUser,
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

    public function destroy(string $id): Response
    {
        $this->deleteUser->execute($id);

        return response()->noContent();
    }

    public function subscribe(SubscriptionRequest $request, string $id): JsonResponse
    {
        try {
            $response = $this->subscribeUser->execute($id, (string) $request->validated('station_id'));
        } catch (UserNotFoundException) {
            return response()->json(['message' => 'User not found.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($response->toArray());
    }

    public function unsubscribe(string $id, string $stationId): JsonResponse
    {
        try {
            $response = $this->unsubscribeUser->execute($id, $stationId);
        } catch (UserNotFoundException) {
            return response()->json(['message' => 'User not found.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($response->toArray());
    }
}
