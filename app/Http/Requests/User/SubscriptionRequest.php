<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

final class SubscriptionRequest extends ApiRequest {

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'weather_station_id' => ['required', 'string', 'min:1'],
        ];
    }
}
