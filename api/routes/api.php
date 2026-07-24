<?php

use App\Http\Controllers\API\Auth\SignUpAction;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('signup', SignUpAction::class)
        ->middleware(['guest', 'throttle:5,1'])
        ->name('api.auth.signup');
});
