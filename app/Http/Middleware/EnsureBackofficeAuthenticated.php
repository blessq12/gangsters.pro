<?php

namespace App\Http\Middleware;

use App\Domain\Staff\Entity\Employee;
use App\Domain\Staff\Repository\EmployeeRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $userId = Auth::guard('web')->id();
        if ($userId === null) {
            return response()->json(['message' => 'Требуется вход.'], 401);
        }

        $employee = $this->employees->findById((int) $userId);
        if (! $employee instanceof Employee || ! $employee->isActive()) {
            Auth::guard('web')->logout();

            return response()->json(['message' => 'Требуется вход.'], 401);
        }

        return $next($request);
    }
}
