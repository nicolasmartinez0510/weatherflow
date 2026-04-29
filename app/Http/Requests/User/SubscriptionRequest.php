<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\ApiRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;
use WeatherFlow\Domain\Repository\WeatherStationRepository;
use WeatherFlow\Domain\ValueObject\WeatherStationId;

final class SubscriptionRequest extends ApiRequest
{

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'weather_station_id' => ['required', 'string', 'min:1'],
        ];
    }

    protected function passedValidation(): void
    {
        $weatherStationId = (string) $this->validated('weather_station_id');
        $weatherStations = app(WeatherStationRepository::class);

        if ($weatherStations->findById(new WeatherStationId($weatherStationId)) !== null) {
            return;
        }

        throw new HttpResponseException(
            response()->json(['message' => 'Weather Station not found.'], Response::HTTP_NOT_FOUND),
        );
    }
}
