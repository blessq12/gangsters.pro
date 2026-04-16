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
        if (! (bool) config('services.yandex_food.enabled', true)) {
            return response()->json([
                'reason' => 'Yandex Food integration is disabled',
            ], 503);
        }

        $token = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $token);
        $expectedToken = (string) config('services.yandex_food.auth_token', '');
        if (!$token || $token !== $expectedToken) {
            return response()->json([
                "reason" => "Access token has been expired. You should request a new one"
            ], 400);
        }

        return $next($request);
    }
}
