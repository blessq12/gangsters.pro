<?php

namespace App\Http\Controllers\Api;

use App\Application\Staff\Command\DeleteEmployeeUseCase;
use App\Application\Staff\Command\SaveEmployeeUseCase;
use App\Application\Staff\Query\ListEmployeesUseCase;
use App\Domain\Staff\Enum\StaffRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class BackofficeEmployeeController extends Controller
{
    public function __construct(
        private readonly ListEmployeesUseCase $list,
        private readonly SaveEmployeeUseCase $save,
        private readonly DeleteEmployeeUseCase $deleteEmployee,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(['employees' => $this->list->execute()]);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->write($request, null);
    }

    public function update(Request $request, int $employee): JsonResponse
    {
        return $this->write($request, $employee);
    }

    public function destroy(int $employee): JsonResponse
    {
        try {
            $this->deleteEmployee->execute($employee);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['ok' => true]);
    }

    private function write(Request $request, ?int $id): JsonResponse
    {
        $roles = array_map(
            static fn (StaffRole $role): string => $role->value,
            StaffRole::cases(),
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'role' => ['required', 'string', Rule::in($roles)],
            'password' => [$id === null ? 'required' : 'nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $role = StaffRole::from($validated['role']);

        try {
            $employee = $this->save->execute(
                $id,
                $validated['name'],
                $validated['email'],
                $validated['phone'] ?? null,
                $role,
                $validated['password'] ?? null,
                array_key_exists('is_active', $validated)
                    ? (bool) $validated['is_active']
                    : true,
            );
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['employee' => $employee], $id === null ? 201 : 200);
    }
}
