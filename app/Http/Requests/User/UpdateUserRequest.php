<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class UpdateUserRequest extends FormRequest {

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['sometimes', 'string', 'email'],
            'name' => ['sometimes', 'string', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if (! $this->filled('email') && ! $this->filled('name')) {
                $v->errors()->add('name', 'At least one of name or email must be provided.');
            }
        });
    }
}
