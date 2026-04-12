<?php

declare(strict_types=1);

namespace App\Http\Requests\Station;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

final class StoreWeatherStationRequest extends ApiRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'owner_id' => ['required', 'string', 'min:1'],
            'name' => ['required', 'string', 'min:1'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'sensor_model' => ['required', 'string', 'min:1'],
            'status' => ['sometimes', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
