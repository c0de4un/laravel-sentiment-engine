<?php

namespace App\Http\Requests\API\Auth;

use App\Http\Requests\API\APIRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Password;

/**
 * @property-read string       $name
 * @property-read string       $email
 * @property-read string       $password
 * @property-read string|null  $password_confirmation
 * @property-read bool|null    $remember
 */
final class SignUpRequest extends APIRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255'
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email:rfc,dns',
                'min:3',
                'max:255',
                'unique:users,email'
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->uncompromised(),
            ],
            'remember' => [
                'nullable',
                'boolean'
            ],
        ];
    }

    /**
     * Кастомные сообщения об ошибках (опционально, но улучшает DX для фронтенда).
     */
    public function messages(): array
    {
        return [
            'password.uncompromised' => 'Этот пароль был скомпрометирован в утечках данных. Пожалуйста, выберите другой.',
        ];
    }
}
