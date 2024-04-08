<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('auth.login-page');
    }
    public function handle($request, \Closure $next, ...$guards)
    {
        if (!auth('sanctum')->user()) {
            return response([
                'status' => false,
                'message' => 'Не удалось найти пользователя по токену',
            ], 401);
        }

        return $next($request);
    }
}
