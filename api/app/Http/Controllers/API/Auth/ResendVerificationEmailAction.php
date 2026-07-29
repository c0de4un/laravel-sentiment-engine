<?php

namespace App\Http\Controllers\API\Auth;

use App\Services\Mail\SmartMailDispatcher;
use App\Mail\VerifyEmail;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class ResendVerificationEmailAction
{
    public function __construct(private readonly SmartMailDispatcher $dispatcher) {}

    public function __invoke(/* Request $request */): JsonResponse
    {
        $user = auth()->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email уже подтвержден.'
            ], Response::HTTP_OK);
        }

        $this->dispatcher->dispatch(new VerifyEmail($user), $user->email);

        return response()->json([
            'message' => 'Ссылка для подтверждения отправлена повторно.'
        ], Response::HTTP_OK);
    }
}
