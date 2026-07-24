<?php

namespace App\Http\Controllers\API\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Requests\API\Auth\SignUpRequest;
use App\Mail\VerifyEmail;
use App\Models\User;
use App\Services\Mail\SmartMailDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Authentication', description: 'Authentication related endpoints')]
final class SignUpAction
{
    #[OA\Post(
        path: '/api/auth/signup',
        description: 'Create user and send confirmation email.',
        summary: 'New user registration',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['name', 'email', 'password', 'password_confirmation'],
                    properties: [
                        new OA\Property(property: 'name', description: 'Имя пользователя', type: 'string', example: 'Денис', maxLength: 255, minLength: 3),
                        new OA\Property(property: 'email', description: 'Уникальный email адрес', type: 'string', format: 'email', example: 'test@yandex.ru'),
                        new OA\Property(property: 'password', description: 'Пароль (мин. 8 символов, буквы и цифры)', type: 'string', format: 'password', example: 'password123', minLength: 8),
                        new OA\Property(property: 'password_confirmation', description: 'Подтверждение пароля', type: 'string', format: 'password', example: 'password123'),
                        new OA\Property(property: 'remember', description: 'Флаг "Запомнить меня" для продления жизни сессии', type: 'boolean', example: true),
                    ]
                )
            )
        ),
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: 'Успешная регистрация. Сессионная кука установлена.',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'message', type: 'string', example: 'Регистрация успешна. Письмо с подтверждением отправлено на вашу почту.'),
                            new OA\Property(
                                property: 'user',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Денис'),
                                    new OA\Property(property: 'email', type: 'string', example: 'test@yandex.ru'),
                                    new OA\Property(property: 'role', type: 'string', example: 'user', enum: ['user', 'admin']),
                                ],
                                type: 'object'
                            ),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Ошибка валидации данных (например, email уже занят или пароль слишком простой)',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
                            new OA\Property(
                                property: 'errors',
                                description: 'Словарь ошибок валидации, где ключ — имя поля, а значение — массив сообщений',
                                type: 'object',
                                additionalProperties: new OA\AdditionalProperties(
                                    type: 'array',
                                    items: new OA\Items(type: 'string', example: 'The email has already been taken.')
                                )
                            ),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: Response::HTTP_TOO_MANY_REQUESTS,
                description: 'Слишком много попыток регистрации (Rate Limit)',
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'message', type: 'string', example: 'Too Many Attempts.'),
                        ]
                    )
                )
            ),
        ]
    )]
    public function __invoke(SignUpRequest $request, SmartMailDispatcher $dispatcher): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => $validated['password'],
            'role'     => UserRole::User,
            'status'   => UserStatus::Active,
        ]);

        Auth::login($user, $request->boolean('remember'));

        $user->markAsLoggedIn();

        $dispatcher->dispatch(new VerifyEmail($user), $user->email);

        return response()->json([
            'message' => 'Регистрация успешна. Письмо с подтверждением отправлено на вашу почту.',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ], Response::HTTP_CREATED);
    }
}
