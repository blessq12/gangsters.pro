<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Warehouse\Command\AdjustStockLevelUseCase;
use App\Application\Warehouse\Command\SaveIngredientUseCase;
use App\Application\Warehouse\Command\WriteOffStockUseCase;
use App\Application\Warehouse\DTO\AdjustStockLevelDto;
use App\Application\Warehouse\DTO\SaveIngredientDto;
use App\Application\Warehouse\DTO\WriteOffStockDto;
use App\Application\Warehouse\Query\GetBranchIngredientStockUseCase;
use App\Application\Warehouse\Query\GetIngredientUseCase;
use App\Application\Warehouse\Query\ListIngredientsUseCase;
use App\Application\Warehouse\Query\ListStockLevelsUseCase;
use App\Application\Warehouse\Query\ListWriteOffsUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Staff\Model\EmployeeModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class WarehouseController extends Controller
{
    public function ingredients(Request $request, ListIngredientsUseCase $useCase): JsonResponse
    {
        return response()->json($useCase->execute(
            offset: (int) $request->query('offset', 0),
            limit: (int) $request->query('limit', 20),
        ));
    }

    public function showIngredient(int $id, GetIngredientUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function storeIngredient(Request $request, SaveIngredientUseCase $useCase): JsonResponse
    {
        return $this->saveIngredient($request, $useCase, null);
    }

    public function updateIngredient(Request $request, int $id, SaveIngredientUseCase $useCase): JsonResponse
    {
        return $this->saveIngredient($request, $useCase, $id);
    }

    public function branchStock(Request $request, int $branchId, ListStockLevelsUseCase $useCase): JsonResponse
    {
        if ($denied = $this->denyIfKitchenForeignBranch($request, $branchId)) {
            return $denied;
        }

        return response()->json($useCase->execute(
            branchId: $branchId,
            offset: (int) $request->query('offset', 0),
            limit: (int) $request->query('limit', 20),
        ));
    }

    public function showBranchIngredient(
        Request $request,
        int $branchId,
        int $id,
        GetBranchIngredientStockUseCase $useCase,
    ): JsonResponse {
        if ($denied = $this->denyIfKitchenForeignBranch($request, $branchId)) {
            return $denied;
        }

        try {
            return response()->json($useCase->execute($branchId, $id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function adjustBranchStock(
        Request $request,
        int $branchId,
        AdjustStockLevelUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'ingredient_id' => ['required', 'integer', 'min:1'],
            'delta' => ['required', 'numeric'],
        ]);

        try {
            $result = $useCase->execute(new AdjustStockLevelDto(
                branchId: $branchId,
                ingredientId: (int) $validated['ingredient_id'],
                delta: (float) $validated['delta'],
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function branchWriteOffs(Request $request, int $branchId, ListWriteOffsUseCase $useCase): JsonResponse
    {
        if ($denied = $this->denyIfKitchenForeignBranch($request, $branchId)) {
            return $denied;
        }

        return response()->json($useCase->execute(
            branchId: $branchId,
            offset: (int) $request->query('offset', 0),
            limit: (int) $request->query('limit', 20),
        ));
    }

    public function storeBranchWriteOff(
        Request $request,
        int $branchId,
        WriteOffStockUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'ingredient_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'reason' => ['required', 'string', 'max:500'],
            'kitchen_ticket_id' => ['nullable', 'integer', 'min:1'],
        ]);

        try {
            $result = $useCase->execute(new WriteOffStockDto(
                branchId: $branchId,
                ingredientId: (int) $validated['ingredient_id'],
                quantity: (float) $validated['quantity'],
                reason: $validated['reason'],
                kitchenTicketId: isset($validated['kitchen_ticket_id'])
                    ? (int) $validated['kitchen_ticket_id']
                    : null,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, 201);
    }

    /**
     * Кухня читает только склад своего филиала смены.
     */
    private function denyIfKitchenForeignBranch(Request $request, int $branchId): ?JsonResponse
    {
        if (! config('backoffice.auth_required')) {
            return null;
        }

        $user = $request->user('sanctum');
        if (! $user instanceof EmployeeModel) {
            return null;
        }

        if ((string) $user->role !== 'kitchen') {
            return null;
        }

        $ownBranchId = (int) ($user->branch_id ?? 0);
        if ($ownBranchId <= 0 || $ownBranchId !== $branchId) {
            return response()->json(['message' => 'Недостаточно прав.'], 403);
        }

        return null;
    }

    private function saveIngredient(
        Request $request,
        SaveIngredientUseCase $useCase,
        ?int $id,
    ): JsonResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:64'],
        ]);

        try {
            $result = $useCase->execute(new SaveIngredientDto(
                id: $id,
                name: $validated['name'],
                unit: $validated['unit'],
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result['ingredient'], $result['created'] ? 201 : 200);
    }
}
