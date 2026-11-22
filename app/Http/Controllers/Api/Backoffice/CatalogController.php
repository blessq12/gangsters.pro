<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Catalog\Command\AddCatalogItemImageUseCase;
use App\Application\Catalog\Command\DeleteCatalogItemImageUseCase;
use App\Application\Catalog\Command\DeleteCategoryUseCase;
use App\Application\Catalog\Command\DeleteProductUseCase;
use App\Application\Catalog\Command\DeleteSetUseCase;
use App\Application\Catalog\Command\DeleteTagUseCase;
use App\Application\Catalog\Command\ReorderCategoriesUseCase;
use App\Application\Catalog\Command\ReplaceCategoryItemsUseCase;
use App\Application\Catalog\Command\ReplaceSetLinesUseCase;
use App\Application\Catalog\Command\SaveCategoryUseCase;
use App\Application\Catalog\Command\SaveProductUseCase;
use App\Application\Catalog\Command\SaveSetUseCase;
use App\Application\Catalog\Command\SaveTagUseCase;
use App\Application\Catalog\Command\UpdateCatalogItemImageUseCase;
use App\Application\Catalog\DTO\SaveCategoryDto;
use App\Application\Catalog\DTO\SaveProductDto;
use App\Application\Catalog\DTO\SaveSetDto;
use App\Application\Catalog\DTO\SaveTagDto;
use App\Application\Catalog\Query\GetAdminCatalogUseCase;
use App\Application\Catalog\Query\GetAdminCategoryUseCase;
use App\Application\Catalog\Query\GetAdminProductUseCase;
use App\Application\Catalog\Query\GetAdminSetUseCase;
use App\Application\Catalog\Query\ListAdminCategoriesUseCase;
use App\Application\Catalog\Query\ListAdminProductsUseCase;
use App\Application\Catalog\Query\ListAdminSetsUseCase;
use App\Application\Catalog\Query\ListAdminTagsUseCase;
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

    public function listProducts(Request $request, ListAdminProductsUseCase $useCase): JsonResponse
    {
        $q = $request->query('q');

        return response()->json($useCase->execute(
            offset: (int) $request->query('offset', 0),
            limit: (int) $request->query('limit', 20),
            q: is_string($q) ? $q : null,
        ));
    }

    public function listSets(Request $request, ListAdminSetsUseCase $useCase): JsonResponse
    {
        $q = $request->query('q');

        return response()->json($useCase->execute(
            offset: (int) $request->query('offset', 0),
            limit: (int) $request->query('limit', 20),
            q: is_string($q) ? $q : null,
        ));
    }

    public function listCategories(Request $request, ListAdminCategoriesUseCase $useCase): JsonResponse
    {
        return response()->json($useCase->execute(
            offset: (int) $request->query('offset', 0),
            limit: (int) $request->query('limit', 20),
        ));
    }

    public function listTags(Request $request, ListAdminTagsUseCase $useCase): JsonResponse
    {
        $q = $request->query('q');

        return response()->json($useCase->execute(
            offset: (int) $request->query('offset', 0),
            limit: (int) $request->query('limit', 20),
            q: is_string($q) ? $q : null,
        ));
    }

    public function showProduct(int $id, GetAdminProductUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function showSet(int $id, GetAdminSetUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function showCategory(int $id, GetAdminCategoryUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function storeProduct(Request $request, SaveProductUseCase $useCase): JsonResponse
    {
        return $this->saveProduct($request, $useCase, null);
    }

    public function updateProduct(Request $request, int $id, SaveProductUseCase $useCase): JsonResponse
    {
        return $this->saveProduct($request, $useCase, $id);
    }

    public function destroyProduct(int $id, DeleteProductUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function storeCategory(Request $request, SaveCategoryUseCase $useCase): JsonResponse
    {
        return $this->saveCategory($request, $useCase, null);
    }

    public function updateCategory(Request $request, int $id, SaveCategoryUseCase $useCase): JsonResponse
    {
        return $this->saveCategory($request, $useCase, $id);
    }

    public function destroyCategory(int $id, DeleteCategoryUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function reorderCategories(Request $request, ReorderCategoriesUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'min:1'],
        ]);

        try {
            return response()->json($useCase->execute(array_map('intval', $validated['ids'])));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function storeSet(Request $request, SaveSetUseCase $useCase): JsonResponse
    {
        return $this->saveSet($request, $useCase, null);
    }

    public function updateSet(Request $request, int $id, SaveSetUseCase $useCase): JsonResponse
    {
        return $this->saveSet($request, $useCase, $id);
    }

    public function destroySet(int $id, DeleteSetUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function storeTag(Request $request, SaveTagUseCase $useCase): JsonResponse
    {
        return $this->saveTag($request, $useCase, null);
    }

    public function updateTag(Request $request, int $id, SaveTagUseCase $useCase): JsonResponse
    {
        return $this->saveTag($request, $useCase, $id);
    }

    public function destroyTag(int $id, DeleteTagUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function replaceCategoryItems(
        Request $request,
        int $id,
        ReplaceCategoryItemsUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.item_id' => ['required', 'integer', 'min:1'],
            'items.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            return response()->json($useCase->execute($id, $validated['items']));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function replaceSetLines(
        Request $request,
        int $id,
        ReplaceSetLinesUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'lines' => ['required', 'array'],
            'lines.*.product_id' => ['required', 'integer', 'min:1'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            return response()->json($useCase->execute($id, $validated['lines']));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function storeItemImage(
        Request $request,
        int $id,
        AddCatalogItemImageUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'file' => ['required', 'file', 'image', 'max:10240'],
            'focus_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'focus_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $focusX = array_key_exists('focus_x', $validated) ? (float) $validated['focus_x'] : null;
        $focusY = array_key_exists('focus_y', $validated) ? (float) $validated['focus_y'] : null;
        if (($focusX === null) xor ($focusY === null)) {
            return response()->json(['message' => 'focus_x и focus_y задаются только парой.'], 422);
        }

        try {
            $result = $useCase->execute($id, $validated['file'], $focusX, $focusY);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, 201);
    }

    public function updateItemImage(
        Request $request,
        int $id,
        int $imageId,
        UpdateCatalogItemImageUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'focus_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'focus_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $focusX = array_key_exists('focus_x', $validated) ? (float) $validated['focus_x'] : null;
        $focusY = array_key_exists('focus_y', $validated) ? (float) $validated['focus_y'] : null;
        if (($focusX === null) xor ($focusY === null)) {
            return response()->json(['message' => 'focus_x и focus_y задаются только парой.'], 422);
        }

        try {
            return response()->json($useCase->execute(
                $id,
                $imageId,
                $focusX,
                $focusY,
                isset($validated['sort_order']) ? (int) $validated['sort_order'] : null,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function destroyItemImage(
        int $id,
        int $imageId,
        DeleteCatalogItemImageUseCase $useCase,
    ): JsonResponse {
        try {
            return response()->json($useCase->execute($id, $imageId));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    private function saveProduct(Request $request, SaveProductUseCase $useCase, ?int $id): JsonResponse
    {
        $request->validate([
            'payload' => ['required', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'image', 'max:10240'],
            'images_meta' => ['nullable', 'string'],
            'existing_images' => ['nullable', 'string'],
            'deleted_image_ids' => ['nullable', 'string'],
        ]);

        try {
            $payload = $this->decodeJsonObject($request->input('payload'), 'payload');
            $imagesMeta = $this->decodeJsonList($request->input('images_meta', '[]'), 'images_meta');
            $existingImages = $this->decodeJsonList($request->input('existing_images', '[]'), 'existing_images');
            $deletedImageIds = $this->decodeJsonList($request->input('deleted_image_ids', '[]'), 'deleted_image_ids');
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $status = (string) ($payload['status'] ?? '');
        $pricesRaw = $payload['prices'] ?? null;
        if ($name === '' || $status === '' || ! is_array($pricesRaw) || $pricesRaw === []) {
            return response()->json(['message' => 'payload: нужны name, status и prices.'], 422);
        }

        $prices = [];
        foreach ($pricesRaw as $row) {
            if (! is_array($row)) {
                continue;
            }
            $type = (string) ($row['type'] ?? '');
            if (! in_array($type, ['regular', 'old'], true)) {
                return response()->json(['message' => 'Недопустимый тип цены: '.$type], 422);
            }
            if (! array_key_exists('amount', $row) || ! is_numeric($row['amount'])) {
                return response()->json(['message' => 'У цены нужна сумма.'], 422);
            }
            $prices[] = [
                'type' => $type,
                'amount' => (int) $row['amount'],
            ];
        }

        $files = $request->file('images', []);
        if (! is_array($files)) {
            $files = $files !== null ? [$files] : [];
        }
        $files = array_values(array_filter($files));

        $newImages = [];
        foreach ($files as $index => $file) {
            $meta = is_array($imagesMeta[$index] ?? null) ? $imagesMeta[$index] : [];
            $focusX = array_key_exists('focus_x', $meta) ? (float) $meta['focus_x'] : 50.0;
            $focusY = array_key_exists('focus_y', $meta) ? (float) $meta['focus_y'] : 50.0;
            $newImages[] = [
                'file' => $file,
                'focus_x' => $focusX,
                'focus_y' => $focusY,
                'sort_order' => array_key_exists('sort_order', $meta) && $meta['sort_order'] !== null
                    ? (int) $meta['sort_order']
                    : $index,
            ];
        }

        $existingNormalized = [];
        foreach ($existingImages as $index => $row) {
            if (! is_array($row) || ! isset($row['id'])) {
                continue;
            }
            $existingNormalized[] = [
                'id' => (int) $row['id'],
                'focus_x' => array_key_exists('focus_x', $row) ? (float) $row['focus_x'] : null,
                'focus_y' => array_key_exists('focus_y', $row) ? (float) $row['focus_y'] : null,
                'sort_order' => array_key_exists('sort_order', $row)
                    ? (int) $row['sort_order']
                    : $index,
            ];
        }

        $deletedNormalized = [];
        foreach ($deletedImageIds as $imageId) {
            if (is_numeric($imageId) && (int) $imageId > 0) {
                $deletedNormalized[] = (int) $imageId;
            }
        }

        $ingredients = [];
        if (isset($payload['ingredients']) && is_array($payload['ingredients'])) {
            $ingredients = array_values(array_map('strval', $payload['ingredients']));
        }

        $nutrition = null;
        if (isset($payload['nutrition']) && is_array($payload['nutrition'])) {
            $nutrition = $payload['nutrition'];
        }

        $tagIds = [];
        if (isset($payload['tag_ids']) && is_array($payload['tag_ids'])) {
            $tagIds = array_map('intval', $payload['tag_ids']);
        }

        try {
            $result = $useCase->execute(new SaveProductDto(
                id: $id,
                name: $name,
                prices: $prices,
                weight: isset($payload['weight']) && $payload['weight'] !== null && $payload['weight'] !== ''
                    ? (int) $payload['weight']
                    : null,
                status: $status,
                description: isset($payload['description']) ? (string) $payload['description'] : null,
                sku: isset($payload['sku']) ? (string) $payload['sku'] : null,
                ingredients: $ingredients,
                nutrition: $nutrition,
                tagIds: $tagIds,
                newImages: $newImages,
                existingImages: $existingNormalized,
                deletedImageIds: $deletedNormalized,
                isRoll: (bool) ($payload['is_roll'] ?? false),
                isComplement: (bool) ($payload['is_complement'] ?? false),
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, $id === null ? 201 : 200);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonObject(mixed $raw, string $field): array
    {
        if (! is_string($raw) || trim($raw) === '') {
            throw new InvalidArgumentException($field.': ожидается JSON-объект.');
        }
        $decoded = json_decode($raw, true);
        if (! is_array($decoded) || array_is_list($decoded)) {
            throw new InvalidArgumentException($field.': ожидается JSON-объект.');
        }

        return $decoded;
    }

    /**
     * @return list<mixed>
     */
    private function decodeJsonList(mixed $raw, string $field): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }
        if (! is_string($raw)) {
            throw new InvalidArgumentException($field.': ожидается JSON-массив.');
        }
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            throw new InvalidArgumentException($field.': ожидается JSON-массив.');
        }

        return array_values($decoded);
    }

    private function saveCategory(Request $request, SaveCategoryUseCase $useCase, ?int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'is_accompanying' => ['sometimes', 'boolean'],
            'items' => ['sometimes', 'array'],
            'items.*.item_id' => ['required', 'integer', 'min:1'],
            'items.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $items = null;
        if (array_key_exists('items', $validated)) {
            $items = [];
            foreach ($validated['items'] as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $items[] = [
                    'item_id' => (int) $row['item_id'],
                    'sort_order' => array_key_exists('sort_order', $row) && $row['sort_order'] !== null
                        ? (int) $row['sort_order']
                        : $index,
                ];
            }
        }

        try {
            $result = $useCase->execute(new SaveCategoryDto(
                id: $id,
                name: $validated['name'],
                isActive: (bool) $validated['is_active'],
                isAccompanying: (bool) ($validated['is_accompanying'] ?? false),
                items: $items,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, $id === null ? 201 : 200);
    }

    private function saveSet(Request $request, SaveSetUseCase $useCase, ?int $id): JsonResponse
    {
        $request->validate([
            'payload' => ['required', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'image', 'max:10240'],
            'images_meta' => ['nullable', 'string'],
            'existing_images' => ['nullable', 'string'],
            'deleted_image_ids' => ['nullable', 'string'],
        ]);

        try {
            $payload = $this->decodeJsonObject($request->input('payload'), 'payload');
            $imagesMeta = $this->decodeJsonList($request->input('images_meta', '[]'), 'images_meta');
            $existingImages = $this->decodeJsonList($request->input('existing_images', '[]'), 'existing_images');
            $deletedImageIds = $this->decodeJsonList($request->input('deleted_image_ids', '[]'), 'deleted_image_ids');
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $status = (string) ($payload['status'] ?? '');
        $pricesRaw = $payload['prices'] ?? null;
        if ($name === '' || $status === '' || ! is_array($pricesRaw) || $pricesRaw === []) {
            return response()->json(['message' => 'payload: нужны name, status и prices.'], 422);
        }

        $prices = [];
        foreach ($pricesRaw as $row) {
            if (! is_array($row)) {
                continue;
            }
            $type = (string) ($row['type'] ?? '');
            if (! in_array($type, ['regular', 'old'], true)) {
                return response()->json(['message' => 'Недопустимый тип цены: '.$type], 422);
            }
            if (! array_key_exists('amount', $row) || ! is_numeric($row['amount'])) {
                return response()->json(['message' => 'У цены нужна сумма.'], 422);
            }
            $prices[] = [
                'type' => $type,
                'amount' => (int) $row['amount'],
            ];
        }

        $files = $request->file('images', []);
        if (! is_array($files)) {
            $files = $files !== null ? [$files] : [];
        }
        $files = array_values(array_filter($files));

        $newImages = [];
        foreach ($files as $index => $file) {
            $meta = is_array($imagesMeta[$index] ?? null) ? $imagesMeta[$index] : [];
            $focusX = array_key_exists('focus_x', $meta) ? (float) $meta['focus_x'] : 50.0;
            $focusY = array_key_exists('focus_y', $meta) ? (float) $meta['focus_y'] : 50.0;
            $newImages[] = [
                'file' => $file,
                'focus_x' => $focusX,
                'focus_y' => $focusY,
                'sort_order' => array_key_exists('sort_order', $meta) && $meta['sort_order'] !== null
                    ? (int) $meta['sort_order']
                    : $index,
            ];
        }

        $existingNormalized = [];
        foreach ($existingImages as $index => $row) {
            if (! is_array($row) || ! isset($row['id'])) {
                continue;
            }
            $existingNormalized[] = [
                'id' => (int) $row['id'],
                'focus_x' => array_key_exists('focus_x', $row) ? (float) $row['focus_x'] : null,
                'focus_y' => array_key_exists('focus_y', $row) ? (float) $row['focus_y'] : null,
                'sort_order' => array_key_exists('sort_order', $row)
                    ? (int) $row['sort_order']
                    : $index,
            ];
        }

        $deletedNormalized = [];
        foreach ($deletedImageIds as $imageId) {
            if (is_numeric($imageId) && (int) $imageId > 0) {
                $deletedNormalized[] = (int) $imageId;
            }
        }

        $lines = null;
        if (array_key_exists('lines', $payload)) {
            if (! is_array($payload['lines'])) {
                return response()->json(['message' => 'payload.lines: ожидается массив.'], 422);
            }
            $lines = [];
            foreach ($payload['lines'] as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $productId = (int) ($row['product_id'] ?? 0);
                $quantity = (int) ($row['quantity'] ?? 0);
                if ($productId < 1 || $quantity < 1) {
                    return response()->json(['message' => 'У позиции состава нужны product_id и quantity ≥ 1.'], 422);
                }
                $lines[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                ];
            }
        }

        try {
            $result = $useCase->execute(new SaveSetDto(
                id: $id,
                name: $name,
                sku: isset($payload['sku']) ? (string) $payload['sku'] : null,
                prices: $prices,
                status: $status,
                description: isset($payload['description']) ? (string) $payload['description'] : null,
                lines: $lines,
                newImages: $newImages,
                existingImages: $existingNormalized,
                deletedImageIds: $deletedNormalized,
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
