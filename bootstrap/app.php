<?php

use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Application;
use App\Http\Middleware\EnsureApiHeaders;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
        $middleware->append(EnsureApiHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::fail(errorMessage: 'unauthorized error', statusCode: 401);
            }
        });
    })->create();
