<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Company\Command\CloneBranchSettingsUseCase;
use App\Application\Company\Command\DeleteBranchMenuItemUseCase;
use App\Application\Company\Command\SaveBranchMenuItemUseCase;
use App\Application\Company\DTO\CloneBranchSettingsDto;
use App\Application\Company\DTO\SaveBranchMenuItemDto;
use App\Application\Company\Query\GetAdminBranchMenuUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Меню филиала: список, upsert, удаление и клонирование настроек между филиалами.
 */
final class BranchMenuController extends Controller
{
    public function index(int $branchId, GetAdminBranchMenuUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($branchId));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function upsert(
        Request $request,
        int $branchId,
        int $catalogItemId,
        SaveBranchMenuItemUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'is_available' => ['required', 'boolean'],
            'price_kopecks' => ['required', 'integer', 'min:0'],
        ]);

        try {
            $result = $useCase->execute(new SaveBranchMenuItemDto(
                branchId: $branchId,
                catalogItemId: $catalogItemId,
                isAvailable: (bool) $validated['is_available'],
                priceKopecks: (int) $validated['price_kopecks'],
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function destroy(
        int $branchId,
        int $catalogItemId,
        DeleteBranchMenuItemUseCase $useCase,
    ): JsonResponse {
        try {
            return response()->json($useCase->execute($branchId, $catalogItemId));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    /**
     * Клонирование меню / политик / доставки с одного филиала на целевые.
     */
    public function cloneSettings(Request $request, CloneBranchSettingsUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'source_branch_id' => ['required', 'integer', 'min:1'],
            'target_branch_ids' => ['required', 'array', 'min:1'],
            'target_branch_ids.*' => ['required', 'integer', 'min:1'],
            'copy_menu' => ['sometimes', 'boolean'],
            'copy_promotions' => ['sometimes', 'boolean'],
            'copy_delivery' => ['sometimes', 'boolean'],
            'mode' => ['sometimes', 'string', 'in:overwrite,skip_existing'],
        ]);

        try {
            $result = $useCase->execute(new CloneBranchSettingsDto(
                sourceBranchId: (int) $validated['source_branch_id'],
                targetBranchIds: array_map('intval', $validated['target_branch_ids']),
                copyMenu: (bool) ($validated['copy_menu'] ?? true),
                copyPromotions: (bool) ($validated['copy_promotions'] ?? false),
                copyDelivery: (bool) ($validated['copy_delivery'] ?? false),
                mode: (string) ($validated['mode'] ?? 'overwrite'),
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }
}
