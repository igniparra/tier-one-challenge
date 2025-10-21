<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\OrderController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('orders')->as('orders.')->group(function () {
        Route::post('/{client_id}', [OrderController::class, 'store'])->name('store');
        Route::get('/{id}', [OrderController::class, 'show'])->name('show');
    });
    Route::prefix('clients')->as('clients.')->group(function () {
        Route::get('/{client}/orders', [OrderController::class, 'byClient'])->name('orders.index');
    });
});
