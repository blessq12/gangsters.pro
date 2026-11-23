<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Catalog\Command\AddCatalogItemImageUseCase;
use App\Application\Catalog\Command\DeleteCatalogItemImageUseCase;
use App\Application\Catalog\Command\DeleteProductUseCase;
use App\Application\Catalog\Command\SaveProductUseCase;
use App\Application\Catalog\Command\UpdateCatalogItemImageUseCase;
use App\Application\Catalog\DTO\SaveProductDto;
use App\Application\Catalog\Query\GetAdminCatalogUseCase;
use App\Application\Catalog\Query\GetAdminProductUseCase;
use App\Application\Catalog\Query\ListAdminProductsUseCase;
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

    public function showProduct(int $id, GetAdminProductUseCase $useCase): JsonResponse
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
        if ($name === '' || $status === '') {
            return response()->json(['message' => 'payload: нужны name и status.'], 422);
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

        $techCardLines = [];
        $rawTechCard = $payload['tech_card'] ?? null;
        $rawLines = is_array($rawTechCard) && isset($rawTechCard['lines']) && is_array($rawTechCard['lines'])
            ? $rawTechCard['lines']
            : [];
        foreach ($rawLines as $row) {
            if (! is_array($row)) {
                continue;
            }
            $ref = trim((string) ($row['ingredient_ref'] ?? ''));
            $unit = trim((string) ($row['unit'] ?? ''));
            $qty = isset($row['quantity']) ? (float) $row['quantity'] : 0.0;
            if ($ref === '' || $unit === '' || $qty <= 0) {
                continue;
            }
            $techCardLines[] = [
                'ingredient_ref' => $ref,
                'quantity' => $qty,
                'unit' => $unit,
            ];
        }

        try {
            $result = $useCase->execute(new SaveProductDto(
                id: $id,
                name: $name,
                weight: isset($payload['weight']) && $payload['weight'] !== null && $payload['weight'] !== ''
                    ? (int) $payload['weight']
                    : null,
                status: $status,
                description: isset($payload['description']) ? (string) $payload['description'] : null,
                sku: isset($payload['sku']) ? (string) $payload['sku'] : null,
                ingredients: $ingredients,
                nutrition: $nutrition,
                newImages: $newImages,
                existingImages: $existingNormalized,
                deletedImageIds: $deletedNormalized,
                isRoll: (bool) ($payload['is_roll'] ?? false),
                techCardLines: $techCardLines,
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

}
