<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Requests\API\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;

final readonly class VerifyEmailAction
{
    public function __invoke(EmailVerificationRequest $request): JsonResponse
    {
        $request->fulfill();

        return response()->json([
            'message' => 'Email успешно подтверждён.',
        ]);
    }
}
