<?php

namespace App\Http\Middleware;

use App\Infrastructure\Client\Model\UR_Client;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Подставляет пользователя Sanctum при валидном Bearer, без 401 если токена нет или он невалиден.
 */
final class AttemptSanctumAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        $raw = $request->bearerToken();
        if ($raw === null || $raw === '') {
            return $next($request);
        }

        $accessToken = PersonalAccessToken::findToken($raw);
        if ($accessToken === null) {
            return $next($request);
        }

        if ($accessToken->expires_at !== null && $accessToken->expires_at->isPast()) {
            return $next($request);
        }

        $tokenable = $accessToken->tokenable;
        if ($tokenable instanceof UR_Client) {
            Auth::guard('sanctum')->setUser($tokenable);
        }

        return $next($request);
    }
}
