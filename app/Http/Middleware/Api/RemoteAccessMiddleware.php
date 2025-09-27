<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RemoteAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // HTTP/2 нормализует заголовки в маленькие буквы
        $secret = $request->header('remote_control_secret');
        $expectedSecret = env('REMOTE_CONTROL_SECRET');

        // Временная отладка
        if ($secret !== $expectedSecret) {
            return response()->json([
                'message' => 'Unauthorized',
                'debug' => [
                    'received' => $secret,
                    'expected' => $expectedSecret,
                    'headers' => $request->headers->all()
                ]
            ], 401);
        }

        return $next($request);
    }
}
