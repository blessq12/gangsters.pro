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
        // Пробуем разные способы передачи секрета
        $secret = $request->header('x-api-key') 
                ?? $request->header('X-API-Key')
                ?? $request->header('x-remote-secret')
                ?? $request->header('X-Remote-Secret')
                ?? $request->get('secret');

        $expectedSecret = env('REMOTE_CONTROL_SECRET');

        if ($secret !== $expectedSecret) {
            return response()->json([
                'message' => 'Unauthorized',
                'debug' => [
                    'received' => $secret,
                    'expected' => $expectedSecret,
                    'all_headers' => $request->headers->all()
                ]
            ], 401);
        }

        return $next($request);
    }
}
