<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Warehouse\Command\AdjustStockLevelUseCase;
use App\Application\Warehouse\Command\SaveIngredientUseCase;
use App\Application\Warehouse\Command\WriteOffStockUseCase;
use App\Application\Warehouse\DTO\AdjustStockLevelDto;
use App\Application\Warehouse\DTO\SaveIngredientDto;
use App\Application\Warehouse\DTO\WriteOffStockDto;
use App\Application\Warehouse\Query\ListIngredientsUseCase;
use App\Application\Warehouse\Query\ListStockLevelsUseCase;
use App\Application\Warehouse\Query\ListWriteOffsUseCase;
use App\Http\Controllers\Controller;
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

    public function storeIngredient(Request $request, SaveIngredientUseCase $useCase): JsonResponse
    {
        return $this->saveIngredient($request, $useCase, null);
    }

    public function updateIngredient(Request $request, int $id, SaveIngredientUseCase $useCase): JsonResponse
    {
        return $this->saveIngredient($request, $useCase, $id);
    }

    public function stock(Request $request, ListStockLevelsUseCase $useCase): JsonResponse
    {
        return response()->json($useCase->execute(
            offset: (int) $request->query('offset', 0),
            limit: (int) $request->query('limit', 20),
        ));
    }

    public function adjustStock(Request $request, AdjustStockLevelUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'ingredient_id' => ['required', 'integer', 'min:1'],
            'delta' => ['required', 'numeric'],
        ]);

        try {
            $result = $useCase->execute(new AdjustStockLevelDto(
                ingredientId: (int) $validated['ingredient_id'],
                delta: (float) $validated['delta'],
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function writeOffs(Request $request, ListWriteOffsUseCase $useCase): JsonResponse
    {
        return response()->json($useCase->execute(
            offset: (int) $request->query('offset', 0),
            limit: (int) $request->query('limit', 20),
        ));
    }

    public function storeWriteOff(Request $request, WriteOffStockUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'ingredient_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'reason' => ['required', 'string', 'max:500'],
            'kitchen_ticket_id' => ['nullable', 'integer', 'min:1'],
        ]);

        try {
            $result = $useCase->execute(new WriteOffStockDto(
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

    private function saveIngredient(
        Request $request,
        SaveIngredientUseCase $useCase,
        ?int $id,
    ): JsonResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:64'],
            'unit' => ['required', 'string', 'max:64'],
        ]);

        try {
            $result = $useCase->execute(new SaveIngredientDto(
                id: $id,
                name: $validated['name'],
                sku: $validated['sku'] ?? null,
                unit: $validated['unit'],
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result['ingredient'], $result['created'] ? 201 : 200);
    }
}
