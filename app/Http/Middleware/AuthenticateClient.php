<?php

namespace App\Http\Middleware;

use App\Infrastructure\Client\Model\CLN_Client;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bearer-only auth для Client API: не подхватывает web-сессию Filament (guard web).
 */
final class AuthenticateClient
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();

        if (! is_string($bearer) || $bearer === '') {
            throw new AuthenticationException();
        }

        $accessToken = PersonalAccessToken::findToken($bearer);
        $client = $accessToken?->tokenable;

        if (! $client instanceof CLN_Client) {
            throw new AuthenticationException();
        }

        Auth::guard('sanctum')->setUser($client);

        return $next($request);
    }
}
