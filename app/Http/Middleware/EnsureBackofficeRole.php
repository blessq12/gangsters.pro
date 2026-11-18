<?php

namespace App\Http\Middleware;

use App\Domain\Staff\Entity\Employee;
use App\Domain\Staff\Repository\EmployeeRepository;
use App\Infrastructure\Staff\Model\STF_Employee;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureBackofficeRole
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

        $employee = $this->employees->findById((int) $user->id);
        if (! $employee instanceof Employee || ! $employee->isActive()) {
            return response()->json(['message' => 'Требуется вход.'], 401);
        }

        $relativePath = $this->relativePath($request);
        $roles = $this->matchRoles($request->method(), $relativePath);
        if ($roles === null) {
            return response()->json(['message' => 'Недостаточно прав.'], 403);
        }

        if (! in_array($employee->role()->value, $roles, true)) {
            return response()->json(['message' => 'Недостаточно прав.'], 403);
        }

        return $next($request);
    }

    private function relativePath(Request $request): string
    {
        $path = trim($request->path(), '/');
        $prefix = 'api/backoffice/';
        if (str_starts_with($path, $prefix)) {
            return substr($path, strlen($prefix));
        }

        return $path;
    }

    /**
     * @return list<string>|null
     */
    private function matchRoles(string $method, string $path): ?array
    {
        /** @var list<array{method?: string|null, path: string, roles: list<string>}> $rules */
        $rules = config('backoffice.access', []);

        foreach ($rules as $rule) {
            if (! is_array($rule) || ! isset($rule['path'], $rule['roles']) || ! is_array($rule['roles'])) {
                continue;
            }

            $ruleMethod = $rule['method'] ?? null;
            if (is_string($ruleMethod) && strtoupper($ruleMethod) !== strtoupper($method)) {
                continue;
            }

            if ($this->pathMatches((string) $rule['path'], $path)) {
                return array_values(array_map('strval', $rule['roles']));
            }
        }

        return null;
    }

    private function pathMatches(string $pattern, string $path): bool
    {
        $regex = preg_quote($pattern, '#');
        $regex = str_replace('\*', '[^/]+', $regex);
        $regex = preg_replace('/\\\{[^}]+\\\}/', '[^/]+', $regex) ?? $regex;

        return (bool) preg_match('#^'.$regex.'$#i', $path);
    }
}
