<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Archivo demasiado grande (PHP rechaza antes de que llegue a Laravel)
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'El archivo es demasiado grande. Máximo permitido: 10 MB.'], 413);
            }
            return redirect()->back()->with('flash', [
                'type'    => 'error',
                'message' => 'El archivo es demasiado grande. El máximo permitido es 10 MB.',
            ]);
        });
    })->create();
