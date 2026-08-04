<?php

namespace App\Http\Middleware;

use App\Application\YandexFood\Port\YandexFoodAuthenticator;
use App\Infrastructure\YandexFood\Exception\YandexFoodBearerTokenRejectedException;
use App\Infrastructure\YandexFood\Exception\YandexFoodDisabledException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class YandexFoodBearerAuth
{
    public function __construct(
        private readonly YandexFoodAuthenticator $authenticator,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $this->authenticator->authenticateBearer($request->bearerToken());
        } catch (YandexFoodBearerTokenRejectedException $e) {
            return response()->json([
                'reason' => $e->reason(),
            ], 400);
        } catch (YandexFoodDisabledException $e) {
            return response()->json([
                'reason' => $e->getMessage(),
            ], 400);
        }

        return $next($request);
    }
}
