<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::get('/me', 'me')->middleware('auth:sanctum');
    Route::delete('/logout', 'logout')->middleware('auth:sanctum');
});
