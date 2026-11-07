<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureBackofficeEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('backoffice.enabled')) {
            abort(404);
        }

        return $next($request);
    }
}
