<?php

use App\Http\Controllers\API\Analyze\AnalyzeStatusAction;
use App\Http\Controllers\API\Analyze\AnalyzeTextAction;
use App\Http\Controllers\API\Auth\ResendVerificationEmailAction;
use App\Http\Controllers\API\Auth\SignUpAction;
use App\Http\Controllers\API\Auth\SignInAction;
use App\Http\Controllers\API\Auth\VerifyEmailAction;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::get('me', function () {
        return response()->json(request()->user());
    })->name('api.auth.me');

    Route::post('signup', SignUpAction::class)
        ->middleware(['guest', 'throttle:5,1'])
        ->name('api.auth.signup');

    Route::post('signin', SignInAction::class)
        ->middleware(['guest', 'throttle:5,1'])
        ->name('api.auth.signin');

    Route::post('email/verification-notification', ResendVerificationEmailAction::class)
        ->middleware(['auth', 'throttle:5,1'])
        ->name('api.auth.verification.resend');
});

Route::get('/verify-email/{id}/{hash}', VerifyEmailAction::class)
    ->name('verification.verify')
    ->middleware(['signed']);

Route::middleware(['auth'])->group(function () {
    Route::post('analyze', AnalyzeTextAction::class)
        ->middleware(['throttle:10,1', 'auth'])
        ->name('api.analyze.text');

    Route::get('analyze/{id}', AnalyzeStatusAction::class)
        ->middleware(['throttle:30,1', 'auth'])
        ->name('api.analyze.status');
});
