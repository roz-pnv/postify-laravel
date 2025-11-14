<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void
    {
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->api([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], status: 422);
            }

            return null;
        });

        $exceptions->render(function (ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return response()->api([
                    'message' => 'Resource not found',
                ], status: 404);
            }

            return null;
        });

        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->api([
                    'message' => 'Unauthenticated or token expired.',
                ], status: 401);
            }

            return null;
        });

        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                return response()->api([
                    'message' => 'Internal Server Error',
                    'error' => app()->environment('local') ? $e->getMessage() : null,
                ], status: 500);
            }

            return null;
        });
    })->create();
