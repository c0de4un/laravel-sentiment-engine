<?php

namespace App\Http\Controllers\API\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Requests\API\Auth\SignUpRequest;
use App\Mail\VerifyEmail;
use App\Models\User;
use App\Services\Mail\SmartMailDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

final readonly class SignUpAction
{
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

        $user->markAsLoggedIn();

        // Генерируем Sanctum токен (plainTextToken нужно отдать юзеру/положить в куку)
        $token = $user->createToken('auth_token')->plainTextToken;

        $dispatcher->dispatch(new VerifyEmail($user), $user->email);

        $response = response()->json([
            'message' => 'Регистрация успешна. Письмо с подтверждением отправлено на вашу почту.',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ], Response::HTTP_CREATED);

        // Кладем токен в HttpOnly куку, чтобы фронтенд не мог прочитать его через JS (защита от XSS)
        // но браузер автоматически отправлял её с каждым запросом.
        return $response->withCookie(Cookie::make(
            'auth_token',
            $token,
            60 * 24 * 30, // 30 дней
            '/',
            null,         // domain (null = текущий домен)
            config('session.secure'), // true если https, false если http (для локалки)
            true,         // HttpOnly (фронт не читает JS'ом)
            false,        // raw
            'lax'         // SameSite (lax достаточно для API)
        ));
    }
}
