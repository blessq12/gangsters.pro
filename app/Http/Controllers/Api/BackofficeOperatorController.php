<?php

namespace App\Http\Controllers\Api;

use App\Application\Content\Command\DeleteOperatorUseCase;
use App\Application\Content\Command\SaveOperatorUseCase;
use App\Application\Content\Query\ListOperatorsUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BackofficeOperatorController extends Controller
{
    public function __construct(
        private readonly ListOperatorsUseCase $list,
        private readonly SaveOperatorUseCase $save,
        private readonly DeleteOperatorUseCase $deleteOperator,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(['operators' => $this->list->execute()]);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->write($request, null);
    }

    public function update(Request $request, int $operator): JsonResponse
    {
        return $this->write($request, $operator);
    }

    public function destroy(int $operator): JsonResponse
    {
        try {
            $this->deleteOperator->execute($operator);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['ok' => true]);
    }

    private function write(Request $request, ?int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => [$id === null ? 'required' : 'nullable', 'string', 'max:255'],
        ]);

        try {
            $operator = $this->save->execute(
                $id,
                $validated['name'],
                $validated['email'],
                $validated['password'] ?? null,
            );
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['operator' => $operator], $id === null ? 201 : 200);
    }
}
