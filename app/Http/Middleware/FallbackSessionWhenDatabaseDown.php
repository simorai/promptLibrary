<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class FallbackSessionWhenDatabaseDown
{
    /**
     * If the session is database-backed and DB is down, switch to file sessions for this request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('session.driver') !== 'database') {
            return $next($request);
        }

        try {
            DB::connection(config('session.connection'))->getPdo();
        } catch (Throwable) {
            config([
                'session.driver' => 'file',
                'session.connection' => null,
            ]);
        }

        return $next($request);
    }
}