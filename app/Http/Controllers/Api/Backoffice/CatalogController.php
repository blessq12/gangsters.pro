<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Catalog\Command\SaveCategoryUseCase;
use App\Application\Catalog\Command\SaveProductUseCase;
use App\Application\Catalog\Command\SaveSetUseCase;
use App\Application\Catalog\Command\SaveTagUseCase;
use App\Application\Catalog\DTO\SaveCategoryDto;
use App\Application\Catalog\DTO\SaveProductDto;
use App\Application\Catalog\DTO\SaveSetDto;
use App\Application\Catalog\DTO\SaveTagDto;
use App\Application\Catalog\Query\GetAdminCatalogUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class CatalogController extends Controller
{
    public function show(GetAdminCatalogUseCase $useCase): JsonResponse
    {
        return response()->json($useCase->execute());
    }

    public function storeProduct(Request $request, SaveProductUseCase $useCase): JsonResponse
    {
        return $this->saveProduct($request, $useCase, null);
    }

    public function updateProduct(Request $request, int $id, SaveProductUseCase $useCase): JsonResponse
    {
        return $this->saveProduct($request, $useCase, $id);
    }

    public function storeCategory(Request $request, SaveCategoryUseCase $useCase): JsonResponse
    {
        return $this->saveCategory($request, $useCase, null);
    }

    public function updateCategory(Request $request, int $id, SaveCategoryUseCase $useCase): JsonResponse
    {
        return $this->saveCategory($request, $useCase, $id);
    }

    public function storeSet(Request $request, SaveSetUseCase $useCase): JsonResponse
    {
        return $this->saveSet($request, $useCase, null);
    }

    public function updateSet(Request $request, int $id, SaveSetUseCase $useCase): JsonResponse
    {
        return $this->saveSet($request, $useCase, $id);
    }

    public function storeTag(Request $request, SaveTagUseCase $useCase): JsonResponse
    {
        return $this->saveTag($request, $useCase, null);
    }

    public function updateTag(Request $request, int $id, SaveTagUseCase $useCase): JsonResponse
    {
        return $this->saveTag($request, $useCase, $id);
    }

    private function saveProduct(Request $request, SaveProductUseCase $useCase, ?int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'weight' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', 'in:active,archived'],
            'description' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $result = $useCase->execute(new SaveProductDto(
                id: $id,
                name: $validated['name'],
                price: (int) $validated['price'],
                weight: isset($validated['weight']) ? (int) $validated['weight'] : null,
                status: $validated['status'],
                description: $validated['description'] ?? null,
                sku: $validated['sku'] ?? null,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, $id === null ? 201 : 200);
    }

    private function saveCategory(Request $request, SaveCategoryUseCase $useCase, ?int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'is_accompanying' => ['sometimes', 'boolean'],
        ]);

        try {
            $result = $useCase->execute(new SaveCategoryDto(
                id: $id,
                name: $validated['name'],
                isActive: (bool) $validated['is_active'],
                isAccompanying: (bool) ($validated['is_accompanying'] ?? false),
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, $id === null ? 201 : 200);
    }

    private function saveSet(Request $request, SaveSetUseCase $useCase, ?int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'string', 'in:active,archived'],
        ]);

        try {
            $result = $useCase->execute(new SaveSetDto(
                id: $id,
                name: $validated['name'],
                sku: $validated['sku'] ?? null,
                price: (int) $validated['price'],
                status: $validated['status'],
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, $id === null ? 201 : 200);
    }

    private function saveTag(Request $request, SaveTagUseCase $useCase, ?int $id): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:64'],
            'label' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:32'],
            'is_active' => ['required', 'boolean'],
        ]);

        try {
            $result = $useCase->execute(new SaveTagDto(
                id: $id,
                code: $validated['code'],
                label: $validated['label'],
                color: $validated['color'] ?? '#999999',
                isActive: (bool) $validated['is_active'],
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, $id === null ? 201 : 200);
    }
}
