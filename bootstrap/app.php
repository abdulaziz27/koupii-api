<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__ . '/../routes/web.php', api: __DIR__ . '/../routes/api.php', commands: __DIR__ . '/../routes/console.php', health: '/up')
    ->withMiddleware(function (Middleware $middleware): void {
        // $middleware->statefulApi();
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(
                    [
                        'message' => 'CSRF token mismatch. Please refresh and try again.',
                        'error' => 'TokenMismatchException',
                    ],
                    419,
                );
            }
        });

        $exceptions->render(function (HttpException $e, $request) {
            if ($e->getStatusCode() === 419 && $request->expectsJson()) {
                return response()->json(
                    [
                        'message' => 'CSRF token mismatch',
                        'error' => 'HttpException-TokenMismatch',
                    ],
                    419,
                );
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(
                    [
                        'message' => 'Not Found',
                        'error' => 'NotFoundHttpException',
                    ],
                    404,
                );
            }
        });
    })
    ->create();
