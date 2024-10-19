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
        return $next($request);
        // if ($request->input('secret') === env('TELESCOPE_SECRET')) {
        //     return $next($request);
        // }

        // // Call the parent handle method for other authorization checks
        // return parent::handle($request, $next);
    }
}
