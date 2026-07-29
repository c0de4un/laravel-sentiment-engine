<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

final class Authenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?: $request->cookie('auth_token');

        if (!$token) {
            return response()->json(['message' => 'Unauthorized - No token provided'], 401);
        }

        /** @var PersonalAccessToken|null $accessToken */
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken || ($accessToken->expires_at && $accessToken->expires_at->isPast())) {
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

        $request->setUserResolver(fn () => $user);

        auth('sanctum')->setUser($user);

        return $next($request);
    }
}
