<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Staff\Command\SaveEmployeeUseCase;
use App\Application\Staff\Command\ToggleEmployeeActiveUseCase;
use App\Application\Staff\DTO\SaveEmployeeDto;
use App\Application\Staff\Query\GetEmployeeUseCase;
use App\Application\Staff\Query\ListEmployeesUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class EmployeeController extends Controller
{
    public function index(Request $request, ListEmployeesUseCase $useCase): JsonResponse
    {
        $role = $request->query('role');
        $isActiveRaw = $request->query('is_active');

        $isActive = null;
        if ($isActiveRaw !== null && $isActiveRaw !== '') {
            $isActive = filter_var($isActiveRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isActive === null) {
                return response()->json(['message' => 'Некорректный is_active.'], 422);
            }
        }

        $branchIdRaw = $request->query('branch_id');
        $branchId = null;
        if ($branchIdRaw !== null && $branchIdRaw !== '') {
            $branchId = (int) $branchIdRaw;
        }

        try {
            return response()->json($useCase->execute(
                role: is_string($role) ? $role : null,
                isActive: $isActive,
                branchId: $branchId,
                offset: (int) $request->query('offset', 0),
                limit: (int) $request->query('limit', 20),
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function show(int $id, GetEmployeeUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($id));
        } catch (InvalidArgumentException $exception) {
            $status = $exception->getMessage() === 'Сотрудник не найден.' ? 404 : 422;

            return response()->json(['message' => $exception->getMessage()], $status);
        }
    }

    public function store(Request $request, SaveEmployeeUseCase $useCase): JsonResponse
    {
        return $this->save($request, $useCase, null);
    }

    public function update(Request $request, int $id, SaveEmployeeUseCase $useCase): JsonResponse
    {
        return $this->save($request, $useCase, $id);
    }

    public function toggleActive(int $id, ToggleEmployeeActiveUseCase $useCase): JsonResponse
    {
        try {
            $result = $useCase->execute($id);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    private function save(
        Request $request,
        SaveEmployeeUseCase $useCase,
        ?int $id,
    ): JsonResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'role' => ['required', 'string', 'max:64'],
            'branch_id' => ['nullable', 'integer', 'min:1'],
            'password' => [$id === null ? 'required' : 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        try {
            $result = $useCase->execute(new SaveEmployeeDto(
                id: $id,
                name: $validated['name'],
                email: $validated['email'],
                phone: $validated['phone'] ?? null,
                role: $validated['role'],
                password: $validated['password'] ?? null,
                isActive: array_key_exists('is_active', $validated)
                    ? (bool) $validated['is_active']
                    : null,
                branchId: isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result['employee'], $result['created'] ? 201 : 200);
    }
}
