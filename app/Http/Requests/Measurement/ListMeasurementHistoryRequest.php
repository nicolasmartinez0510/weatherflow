<?php

declare(strict_types=1);

namespace App\Http\Requests\Measurement;

use App\Http\Requests\ApiRequest;

final class ListMeasurementHistoryRequest extends ApiRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'station_name' => ['sometimes', 'string', 'min:1'],
            'min_temperature' => ['sometimes', 'numeric'],
            'max_temperature' => ['sometimes', 'numeric'],
            'alerts_only' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->has(['min_temperature', 'max_temperature'])) {
                return;
            }

            $min = $this->input('min_temperature');
            $max = $this->input('max_temperature');

            if ($min !== null && $max !== null && (float) $min > (float) $max) {
                $validator->errors()->add(
                    'min_temperature',
                    'The min temperature must be less than or equal to max temperature.',
                );
            }
        });
    }
}
