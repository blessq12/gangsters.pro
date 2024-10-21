<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Telescope\Http\Middleware\Authorize as TelescopeAuthorize;

class CustomTelescopeAuthorize extends TelescopeAuthorize
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        return env('TELESCOPE_ALLOWED') ? $next($request) : abort(403, 'Unauthorized');
    }
}
