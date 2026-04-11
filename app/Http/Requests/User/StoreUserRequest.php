<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\ApiRequest;

final class StoreUserRequest extends ApiRequest {

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'name' => ['required', 'string', 'min:1'],
        ];
    }
}
