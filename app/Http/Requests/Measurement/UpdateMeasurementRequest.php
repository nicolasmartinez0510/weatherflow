<?php

declare(strict_types=1);

namespace App\Http\Requests\Measurement;

use App\Http\Requests\ApiRequest;

final class UpdateMeasurementRequest extends ApiRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'temperature' => ['sometimes', 'numeric'],
            'humidity' => ['sometimes', 'numeric', 'between:0,100'],
            'pressure' => ['sometimes', 'numeric', 'min:0.01'],
            'reported_at' => ['sometimes', 'date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->hasAny(['temperature', 'humidity', 'pressure', 'reported_at'])) {
                $validator->errors()->add('temperature', 'At least one field must be provided.');
            }
        });
    }
}
