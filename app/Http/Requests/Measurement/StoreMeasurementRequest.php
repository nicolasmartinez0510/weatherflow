<?php

declare(strict_types=1);

namespace App\Http\Requests\Measurement;

use App\Http\Requests\ApiRequest;

final class StoreMeasurementRequest extends ApiRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'weather_station_id' => ['required', 'string', 'min:1'],
            'temperature' => ['required', 'numeric'],
            'humidity' => ['required', 'numeric', 'between:0,100'],
            'pressure' => ['required', 'numeric', 'min:0.01'],
            'reported_at' => ['required', 'date'],
        ];
    }
}
