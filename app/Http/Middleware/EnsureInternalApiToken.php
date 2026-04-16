<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInternalApiToken
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.internal.api_token', '');
        $provided = (string) $request->header('X-Internal-Api-Token', '');

        if ($expected === '' || $provided === '' || ! hash_equals($expected, $provided)) {
            return response()->json([
                'message' => 'Unauthorized internal API access.',
            ], 401);
        }

        return $next($request);
    }
}
