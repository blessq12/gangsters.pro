<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Menu\Command\DeleteBranchCategoryUseCase;
use App\Application\Menu\Command\DeleteBranchSetUseCase;
use App\Application\Menu\Command\DeleteBranchTagUseCase;
use App\Application\Menu\Command\ReorderBranchCategoriesUseCase;
use App\Application\Menu\Command\ReplaceBranchCategoryItemsUseCase;
use App\Application\Menu\Command\SaveBranchCategoryUseCase;
use App\Application\Menu\Command\SaveBranchSetUseCase;
use App\Application\Menu\Command\SaveBranchTagUseCase;
use App\Application\Menu\DTO\SaveBranchCategoryDto;
use App\Application\Menu\DTO\SaveBranchSetDto;
use App\Application\Menu\DTO\SaveBranchTagDto;
use App\Application\Menu\Query\GetBranchCategoryUseCase;
use App\Application\Menu\Query\GetBranchSetUseCase;
use App\Application\Menu\Query\ListBranchCategoriesUseCase;
use App\Application\Menu\Query\ListBranchSetsUseCase;
use App\Application\Menu\Query\ListBranchTagsUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Структура меню филиала: категории, теги и наборы.
 */
final class BranchMenuStructureController extends Controller
{
    // ───────── Категории ─────────

    public function indexCategories(int $branchId, ListBranchCategoriesUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($branchId));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function showCategory(int $branchId, int $id, GetBranchCategoryUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($branchId, $id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function storeCategory(int $branchId, Request $request, SaveBranchCategoryUseCase $useCase): JsonResponse
    {
        return $this->saveCategory($branchId, null, $request, $useCase);
    }

    public function updateCategory(
        int $branchId,
        int $id,
        Request $request,
        SaveBranchCategoryUseCase $useCase,
    ): JsonResponse {
        return $this->saveCategory($branchId, $id, $request, $useCase);
    }

    public function destroyCategory(int $branchId, int $id, DeleteBranchCategoryUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($branchId, $id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function reorderCategories(
        int $branchId,
        Request $request,
        ReorderBranchCategoriesUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'min:1', 'distinct'],
        ]);

        try {
            return response()->json($useCase->execute(
                $branchId,
                array_map('intval', $validated['ids']),
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function replaceCategoryItems(
        int $branchId,
        int $id,
        Request $request,
        ReplaceBranchCategoryItemsUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'items' => ['present', 'array'],
            'items.*.entry_kind' => ['required', 'string', 'in:product,set'],
            'items.*.entry_id' => ['required', 'integer', 'min:1'],
            'items.*.sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        try {
            return response()->json($useCase->execute(
                $branchId,
                $id,
                $this->normalizeCategoryItems($validated['items']),
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    // ───────── Теги ─────────

    public function indexTags(int $branchId, ListBranchTagsUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($branchId));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function storeTag(int $branchId, Request $request, SaveBranchTagUseCase $useCase): JsonResponse
    {
        return $this->saveTag($branchId, null, $request, $useCase);
    }

    public function updateTag(
        int $branchId,
        int $id,
        Request $request,
        SaveBranchTagUseCase $useCase,
    ): JsonResponse {
        return $this->saveTag($branchId, $id, $request, $useCase);
    }

    public function destroyTag(int $branchId, int $id, DeleteBranchTagUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($branchId, $id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    // ───────── Наборы ─────────

    public function indexSets(int $branchId, ListBranchSetsUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($branchId));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function showSet(int $branchId, int $id, GetBranchSetUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($branchId, $id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function storeSet(int $branchId, Request $request, SaveBranchSetUseCase $useCase): JsonResponse
    {
        return $this->saveSet($branchId, null, $request, $useCase);
    }

    public function updateSet(
        int $branchId,
        int $id,
        Request $request,
        SaveBranchSetUseCase $useCase,
    ): JsonResponse {
        return $this->saveSet($branchId, $id, $request, $useCase);
    }

    public function destroySet(int $branchId, int $id, DeleteBranchSetUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($branchId, $id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    // ───────── Приватные хелперы ─────────

    private function saveCategory(
        int $branchId,
        ?int $id,
        Request $request,
        SaveBranchCategoryUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'is_accompanying' => ['sometimes', 'boolean'],
            'is_complement' => ['sometimes', 'boolean'],
            'is_gift' => ['sometimes', 'boolean'],
            'items' => ['sometimes', 'array'],
            'items.*.entry_kind' => ['required', 'string', 'in:product,set'],
            'items.*.entry_id' => ['required', 'integer', 'min:1'],
            'items.*.sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        try {
            $result = $useCase->execute(new SaveBranchCategoryDto(
                branchId: $branchId,
                id: $id,
                name: (string) $validated['name'],
                slug: $validated['slug'] ?? null,
                sortOrder: (int) $validated['sort_order'],
                isActive: (bool) $validated['is_active'],
                isAccompanying: (bool) ($validated['is_accompanying'] ?? false),
                isComplement: (bool) ($validated['is_complement'] ?? false),
                isGift: (bool) ($validated['is_gift'] ?? false),
                items: array_key_exists('items', $validated)
                    ? $this->normalizeCategoryItems($validated['items'])
                    : null,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, $id === null ? 201 : 200);
    }

    private function saveTag(int $branchId, ?int $id, Request $request, SaveBranchTagUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:64'],
            'label' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:32'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        try {
            $result = $useCase->execute(new SaveBranchTagDto(
                branchId: $branchId,
                id: $id,
                code: (string) $validated['code'],
                label: (string) $validated['label'],
                color: (string) $validated['color'],
                isActive: (bool) $validated['is_active'],
                sortOrder: (int) $validated['sort_order'],
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, $id === null ? 201 : 200);
    }

    private function saveSet(int $branchId, ?int $id, Request $request, SaveBranchSetUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_available' => ['required', 'boolean'],
            'price_kopecks' => ['required_without:price_rubles', 'integer', 'min:0'],
            'price_rubles' => ['required_without:price_kopecks', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:active,archived'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.catalog_item_id' => ['required', 'integer', 'min:1'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
            'tag_ids' => ['sometimes', 'array'],
            'tag_ids.*' => ['integer', 'min:1'],
        ]);

        $priceKopecks = array_key_exists('price_kopecks', $validated)
            ? (int) $validated['price_kopecks']
            : (int) round(((float) $validated['price_rubles']) * 100);

        $lines = [];
        foreach ($validated['lines'] as $line) {
            $lines[] = [
                'catalog_item_id' => (int) $line['catalog_item_id'],
                'quantity' => (int) $line['quantity'],
            ];
        }

        try {
            $result = $useCase->execute(new SaveBranchSetDto(
                branchId: $branchId,
                id: $id,
                name: (string) $validated['name'],
                slug: $validated['slug'] ?? null,
                description: $validated['description'] ?? null,
                isAvailable: (bool) $validated['is_available'],
                priceKopecks: $priceKopecks,
                status: (string) $validated['status'],
                lines: $lines,
                tagIds: array_map('intval', $validated['tag_ids'] ?? []),
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, $id === null ? 201 : 200);
    }

    /**
     * @param  array<int, array{entry_kind: string, entry_id: int|string, sort_order?: int|string}>  $items
     * @return list<array{entry_kind: string, entry_id: int, sort_order: int}>
     */
    private function normalizeCategoryItems(array $items): array
    {
        $normalized = [];
        foreach (array_values($items) as $index => $item) {
            $normalized[] = [
                'entry_kind' => (string) $item['entry_kind'],
                'entry_id' => (int) $item['entry_id'],
                'sort_order' => (int) ($item['sort_order'] ?? $index),
            ];
        }

        return $normalized;
    }
}
