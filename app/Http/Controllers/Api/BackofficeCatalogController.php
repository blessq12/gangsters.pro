<?php

namespace App\Http\Controllers\Api;

use App\Application\Catalog\Command\AddProductImageUseCase;
use App\Application\Catalog\Command\SaveAdminCategoryUseCase;
use App\Application\Catalog\Command\SaveAdminProductUseCase;
use App\Application\Catalog\Command\SaveAdminSetUseCase;
use App\Application\Catalog\Command\SaveAdminTagUseCase;
use App\Application\Catalog\Query\GetAdminCatalogUseCase;
use App\Application\Catalog\Query\ListProductImagesUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

final class BackofficeCatalogController extends Controller
{
    public function __construct(
        private readonly GetAdminCatalogUseCase $catalog,
        private readonly SaveAdminCategoryUseCase $saveCategory,
        private readonly SaveAdminTagUseCase $saveTag,
        private readonly SaveAdminProductUseCase $saveProduct,
        private readonly SaveAdminSetUseCase $saveSet,
        private readonly ListProductImagesUseCase $images,
        private readonly AddProductImageUseCase $addImage,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->catalog->execute());
    }

    public function storeCategory(Request $request): JsonResponse
    {
        return $this->writeCategory($request, null);
    }

    public function updateCategory(Request $request, int $category): JsonResponse
    {
        return $this->writeCategory($request, $category);
    }

    public function storeTag(Request $request): JsonResponse
    {
        return $this->writeTag($request, null);
    }

    public function updateTag(Request $request, int $tag): JsonResponse
    {
        return $this->writeTag($request, $tag);
    }

    public function storeProduct(Request $request): JsonResponse
    {
        return $this->writeProduct($request, null);
    }

    public function updateProduct(Request $request, int $product): JsonResponse
    {
        return $this->writeProduct($request, $product);
    }

    public function storeSet(Request $request): JsonResponse
    {
        return $this->writeSet($request, null);
    }

    public function updateSet(Request $request, int $set): JsonResponse
    {
        return $this->writeSet($request, $set);
    }

    public function productImages(int $product): JsonResponse
    {
        try {
            return response()->json(['images' => $this->images->execute($product)]);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function storeProductImage(Request $request, int $product): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:8192'],
        ]);
        $file = $request->file('image');
        if (! $file instanceof UploadedFile) {
            return response()->json(['message' => 'Нужна картинка.'], 422);
        }

        try {
            $this->addImage->execute($product, (string) $file->store('products/'.$product, 'public'));

            return response()->json(['images' => $this->images->execute($product)], 201);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    private function writeCategory(Request $request, ?int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'is_accompanying' => ['required', 'boolean'],
        ]);

        try {
            $category = $this->saveCategory->execute(
                $id,
                $validated['name'],
                (bool) $validated['is_active'],
                (bool) $validated['is_accompanying'],
            );
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['category' => $category], $id === null ? 201 : 200);
    }

    private function writeTag(Request $request, ?int $id): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:64'],
            'label' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:32'],
            'is_active' => ['required', 'boolean'],
        ]);

        try {
            $tag = $this->saveTag->execute(
                $id,
                $validated['code'],
                $validated['label'],
                (string) ($validated['color'] ?? ''),
                (bool) $validated['is_active'],
            );
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['tag' => $tag], $id === null ? 201 : 200);
    }

    private function writeProduct(Request $request, ?int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'old_price' => ['nullable', 'integer', 'min:0'],
            'weight' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', 'in:active,archived'],
            'description' => ['nullable', 'string'],
            'sku' => ['nullable', 'string', 'max:64'],
        ]);

        try {
            $productId = $this->saveProduct->execute(
                $id,
                $validated['name'],
                (int) $validated['price'],
                isset($validated['old_price']) ? (int) $validated['old_price'] : null,
                isset($validated['weight']) ? (int) $validated['weight'] : null,
                $validated['status'],
                $validated['description'] ?? null,
                $validated['sku'] ?? null,
            );
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['id' => $productId], $id === null ? 201 : 200);
    }

    private function writeSet(Request $request, ?int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:64'],
            'price' => ['required', 'integer', 'min:0'],
            'old_price' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', 'in:active,archived'],
        ]);

        try {
            $setId = $this->saveSet->execute(
                $id,
                $validated['name'],
                $validated['sku'] ?? null,
                (int) $validated['price'],
                isset($validated['old_price']) ? (int) $validated['old_price'] : null,
                $validated['status'],
            );
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['id' => $setId], $id === null ? 201 : 200);
    }
}
