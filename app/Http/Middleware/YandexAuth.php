<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class YandexAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $token);
        if (!$token || $token !== env('YANDEX_EDA_AUTH_TOKEN')) {
            return response()->json([
                "reason" => "Access token has been expired. You should request a new one"
            ], 400);
        }

        return $next($request);
    }
}
