<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['userRoleOnly:admin'])->group(function () {
    Route::prefix('/categories')->group(function () {
        Route::get('', [CategoryController::class, 'getAll']);
        Route::post('', [CategoryController::class, 'create']);

        Route::prefix('/{id}')->where(['id' => '[0-9]+'])->group(function () {
            Route::delete('', [CategoryController::class, 'delete']);
            Route::put('', [CategoryController::class, 'update']);
            Route::get('', [CategoryController::class, 'get']);
        });
    });
});

Route::middleware(['userRoleOnly:admin,user'])->group(function () {
    Route::prefix('/products')->group(function () {
        Route::get('', [ProductController::class, 'getAll']);
        Route::post('', [ProductController::class, 'create']);

        Route::prefix('/{id}')->where(['id' => '[0-9]+'])->group(function () {
            Route::delete('', [ProductController::class, 'delete']);
            Route::put('', [ProductController::class, 'update']);
            Route::get('', [ProductController::class, 'get']);
        });
    });

    Route::get('/users/current', [UserController::class, 'current']);
});
