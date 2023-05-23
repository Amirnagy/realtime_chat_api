<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')
    ->as('auth.')
    ->group(function()
    {
        Route::post('register',[AuthController::class,'register'])
            ->name('register');

        Route::post('login',[AuthController::class,'login'])
            ->name('login');

        Route::post('login_with_token',[AuthController::class,'loginWithToken'])
            ->middleware('auth:sanctum')
            ->name('loginWithToken');

        Route::post('logout',[AuthController::class,'logout'])
            ->middleware('auth:sanctum')
            ->name('logout');
    });

Route::middleware('auth:sanctum')->group(function()
    {
        Route::apiResource('chat',ChatController::class)
            ->only('index','store','show');
        Route::apiResource('chat_message',ChatMessageController::class)
            ->only('index','store');
        Route::apiResource('user',UserController::class)
            ->only('index');
    });

