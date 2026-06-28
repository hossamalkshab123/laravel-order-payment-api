<?php

use App\Domain\User\Controllers\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:60,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:60,1');

Route::middleware(['api', ForceJsonResponse::class, 'auth:api', 'throttle:60,1'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::resource('/orders', OrderController::class)->except(['create', 'edit']);

    Route::prefix('/payments')->group(function (): void {
        Route::post('/process', [PaymentController::class, 'process']);
        Route::get('/', [PaymentController::class, 'index']);
        Route::get('/order/{order_id}', [PaymentController::class, 'byOrder']);
    });
});
