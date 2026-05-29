<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\FallbackSessionWhenDatabaseDown;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use PDOException;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);
        $middleware->validateCsrfTokens(except: ['api/*']);

        $middleware->web(prepend: [
            FallbackSessionWhenDatabaseDown::class,
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $exception) {
            if (! isDatabaseConnectivityException($exception)) {
                return null;
            }

            return response()->view('errors.503', status: 503);
        });
    })->create();

/**
 * Match database/network outage errors so we can render a graceful 503 page.
 */
function isDatabaseConnectivityException(Throwable $exception): bool
{
    if ($exception instanceof PDOException) {
        return true;
    }

    if (! $exception instanceof QueryException) {
        return false;
    }

    $message = strtolower($exception->getMessage());

    $signals = [
        'connection refused',
        'connection timed out',
        'could not find driver',
        'failed to open stream',
        'getaddrinfo',
        'host is down',
        'is the server running',
        'network is unreachable',
        'no such file or directory',
        'server has gone away',
        'sqlstate[080',
        'sqlstate[08s',
        'too many connections',
        'unable to connect',
    ];

    foreach ($signals as $signal) {
        if (str_contains($message, $signal)) {
            return true;
        }
    }

    return false;
}
