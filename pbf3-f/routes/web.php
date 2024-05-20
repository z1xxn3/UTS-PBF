<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('', function () {
    return response()->view('welcome');
});

Route::prefix('/api/oauth')->group(function () {
    Route::get('/register', [AuthController::class, 'oAuthRedirect']);
    Route::get('/callback', [AuthController::class, 'oAuthCallback']);
});
