<?php

namespace App\Http\Controllers\API\Auth;

use App\Enums\UserStatus;
use App\Http\Requests\API\Auth\SignInRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class SignInAction
{
    public function __invoke(SignInRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            return response()->json([
                'message' => 'Неверные учетные данные.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        if ($user->status !== UserStatus::Active->value) {
            Auth::logout();

            return response()->json([
                'message' => 'Ваш аккаунт заблокирован или деактивирован. Обратитесь в поддержку.',
            ], Response::HTTP_FORBIDDEN);
        }

        $user->markAsLoggedIn();

        return response()->json([
            'message' => 'Успешный вход в систему.',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role->value,
            ],
        ], Response::HTTP_OK);
    }
}
