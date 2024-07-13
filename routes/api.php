<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TodoController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::get('/me', 'me')->middleware('auth:sanctum');
    Route::delete('/logout', 'logout')->middleware('auth:sanctum');
});

Route::controller(TodoController::class)->prefix('todos')->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{id}', 'show');
    Route::put('/{id}/done', 'done');
    Route::put('/{id}/undone', 'undone');
    Route::delete('/{id}', 'destroy');
})->middleware('auth:sanctum');
