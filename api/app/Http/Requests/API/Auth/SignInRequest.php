<?php

namespace App\Http\Requests\API\Auth;

use App\Http\Requests\API\APIRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property-read string     $email
 * @property-read string     $password
 * @property-read bool|null  $remember
 */
#[OA\Post(
    path: '/api/auth/signin',
    description: 'Аутентифицирует пользователя по email и паролю. При успехе устанавливает HttpOnly сессионную куку (Sanctum SPA mode).',
    summary: 'Вход в систему (Авторизация)',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', description: 'Email адрес пользователя', type: 'string', format: 'email', example: 'code4un@yandex.ru'),
                    new OA\Property(property: 'password', description: 'Пароль пользователя', type: 'string', format: 'password', example: 'SuperSecretPassword123!'),
                    new OA\Property(property: 'remember', description: 'Флаг "Запомнить меня" для продления жизни сессии', type: 'boolean', example: true),
                ]
            )
        )
    ),
    tags: ['Authentication'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Успешная авторизация. Сессионная кука установлена.',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Успешный вход в систему.'),
                        new OA\Property(
                            property: 'user',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Денис Зямаев'),
                                new OA\Property(property: 'email', type: 'string', example: 'code4un@yandex.ru'),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_UNAUTHORIZED,
            description: 'Неверный email или пароль',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Неверные учетные данные.'),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_UNPROCESSABLE_ENTITY,
            description: 'Ошибка валидации данных запроса'
        ),
        new OA\Response(
            response: Response::HTTP_TOO_MANY_REQUESTS,
            description: 'Превышен лимит попыток входа (Rate Limit)'
        ),
    ]
)]
final class SignInRequest extends APIRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'lowercase',
                'email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:64',
            ],
            'remember' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Указан некорректный формат email адреса.',
        ];
    }
}
