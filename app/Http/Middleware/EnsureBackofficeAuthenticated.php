<?php

namespace App\Http\Middleware;

use App\Domain\Staff\Entity\Employee;
use App\Domain\Staff\Repository\EmployeeRepository;
use App\Infrastructure\Staff\Model\STF_Employee;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

final class EnsureBackofficeAuthenticated
{
    public function __construct(
        private readonly EmployeeRepository $employees,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('backoffice.auth_required')) {
            return $next($request);
        }

        $user = $request->user('sanctum');
        if (! $user instanceof STF_Employee) {
            return response()->json(['message' => 'Требуется вход.'], 401);
        }

        $token = $user->currentAccessToken();
        if (
            ! $token instanceof PersonalAccessToken
            || ! PersonalAccessToken::query()->whereKey($token->getKey())->exists()
        ) {
            return response()->json(['message' => 'Требуется вход.'], 401);
        }

        $employee = $this->employees->findById((int) $user->id);
        if (! $employee instanceof Employee || ! $employee->isActive()) {
            return response()->json(['message' => 'Требуется вход.'], 401);
        }

        return $next($request);
    }
}
