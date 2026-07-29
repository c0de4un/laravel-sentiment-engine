<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

final class Authenticate
{
    /**
     * Stateles авторизация по Bearer-токену или кастомной куке.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Пытаемся достать токен из заголовка Authorization (для мобилок/постмана)
        // 2. Если нет, берем из нашей куки auth_token ( для веб-фронтов )
        $token = $request->bearerToken() ?: $request->cookie('auth_token');

        if (!$token) {
            return response()->json(['message' => 'Unauthorized - No token provided'], 401);
        }

        // Ищем токен в БД (Sanctum шифрует токены, метод найдет нужный)
        /** @var PersonalAccessToken|null $accessToken */
        $accessToken = PersonalAccessToken::findToken($token);

        // Проверяем: есть ли токен, привязан ли к нему юзер, и не протух ли он
        if (!$accessToken || ($accessToken->expires_at && $accessToken->expires_at->isPast())) {
            // Опционально: удаляем протухший токен, чтобы не мусорить в БД
            if ($accessToken) {
                $accessToken->delete();
            }
            return response()->json(['message' => 'Unauthorized - Invalid or expired token'], 401);
        }

        /** @var User|null $user */
        $user = $accessToken->tokenable;

        if (!$user) {
            return response()->json(['message' => 'Unauthorized - User not found'], 401);
        }

        // Авторизуем юзера для ТЕКУЩЕГО запроса без создания сессии (Stateless)
        auth('sanctum')->setUser($user);

        return $next($request);
    }
}
