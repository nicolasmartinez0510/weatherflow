<?php

declare(strict_types=1);

namespace App\Http\Requests\Station;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

final class UpdateWeatherStationRequest extends ApiRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:1'],
            'latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'sensor_model' => ['sometimes', 'string', 'min:1'],
            'status' => ['sometimes', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->hasAnyUpdateField()) {
                $validator->errors()->add('name', 'At least one field must be provided.');

                return;
            }

            $hasLat = $this->has('latitude');
            $hasLon = $this->has('longitude');
            if ($hasLat xor $hasLon) {
                if ($hasLat) {
                    $validator->errors()->add('longitude', 'Latitude and longitude must be provided together.');
                } else {
                    $validator->errors()->add('latitude', 'Latitude and longitude must be provided together.');
                }
            }
        });
    }

    // PRIVATE FUNCTIONS

    private function hasAnyUpdateField(): bool
    {
        return $this->hasAny(['name', 'latitude', 'longitude', 'sensor_model', 'status']);
    }
}
