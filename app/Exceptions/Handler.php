<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof AuthenticationException && $request->is('api/*')) {
            return response()->json([
                'message' => $e->getMessage() !== ''
                    ? $e->getMessage()
                    : 'Требуется авторизация.',
            ], 401);
        }

        if ($e instanceof \InvalidArgumentException && $request->is('api/*')) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return parent::render($request, $e);
    }
}
