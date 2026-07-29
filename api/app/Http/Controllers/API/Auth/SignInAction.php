<?php

namespace App\Http\Controllers\API\Auth;

use App\Enums\UserStatus;
use App\Http\Requests\API\Auth\SignInRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

final readonly class SignInAction
{
    public function __invoke(SignInRequest $request): JsonResponse
    {
        // 1. Находим пользователя по email
        $user = User::where('email', $request->string('email'))->first();

        // 2. Проверяем, существует ли юзер и верный ли пароль
        // Hash::check делает это безопасно (защита от timing-атак)
        if (!$user || !Hash::check($request->string('password'), $user->password)) {
            // Возвращаем общую ошибку, чтобы не светить, что именно не так (email или пароль)
            return response()->json([
                'message' => 'Неверные учетные данные.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // 3. Проверяем статус аккаунта (если юзер забанен)
        if ($user->status !== UserStatus::Active->value) {
            return response()->json([
                'message' => 'Ваш аккаунт заблокирован или деактивирован. Обратитесь в поддержку.',
            ], Response::HTTP_FORBIDDEN);
        }

        // 4. Обновляем время последнего входа
        $user->markAsLoggedIn();

        // 5. Создаем Sanctum токен
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = response()->json([
            'message' => 'Успешный вход в систему.',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role->value,
            ],
        ], Response::HTTP_OK);

        // 6. Кладем токен в HttpOnly куку (аналогично регистрации)
        return $response->withCookie(Cookie::make(
            'auth_token',
            $token,
            60 * 24 * 30, // 30 дней
            '/',
            null,
            config('session.secure'),
            true,         // HttpOnly
            false,
            'lax'         // SameSite
        ));
    }
}
