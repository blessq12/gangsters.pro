<?php

namespace App\Exceptions;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Common\Exceptions\UnauthorizedException;
use App\Domain\Client\Event\ClientUnauthorizedAccessDetected;
use App\Domain\Client\Exception\ClientAddressNotFoundException;
use App\Domain\Client\Exception\ClientAlreadyExistsException;
use App\Domain\Client\Exception\ClientNotFoundException;
use App\Domain\Client\Exception\InvalidPasswordResetTokenException;
use App\Domain\Order\Exceptions\OrderInvariantViolation;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Event;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($request->is('api/client/*') && $this->isClientUnauthorized($e)) {
            Event::dispatch(new ClientUnauthorizedAccessDetected(
                path: '/'.$request->path(),
                method: $request->method(),
                ip: (string) ($request->ip() ?? 'n/a'),
                userAgent: $request->userAgent(),
            ));
        }

        if ($e instanceof ApiException && $request->is('api/*')) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->statusCode());
        }

        if ($e instanceof ClientNotFoundException && $request->is('api/*')) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        }

        if ($e instanceof ClientAlreadyExistsException && $request->is('api/*')) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        if ($e instanceof ClientAddressNotFoundException && $request->is('api/*')) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        }

        if ($e instanceof InvalidPasswordResetTokenException && $request->is('api/*')) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        if ($e instanceof OrderInvariantViolation && $request->is('api/*')) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return parent::render($request, $e);
    }

    private function isClientUnauthorized(Throwable $e): bool
    {
        if ($e instanceof AuthenticationException) {
            return true;
        }

        if ($e instanceof UnauthorizedException) {
            return true;
        }

        return false;
    }
}
