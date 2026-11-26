<?php

namespace App\Domain\Supporting\Catalog\Repository;

use App\Domain\Supporting\Catalog\Entity\Product;

interface CatalogItemRepository
{
    public function findProductById(int $id): ?Product;

    /**
     * @param  list<int>  $ids
     * @return list<Product>
     */
    public function findActiveProductsByIds(array $ids): array;

    /**
     * @param  list<int>  $ids
     * @return array<int, string>
     */
    public function findProductNamesByIds(array $ids): array;

    /**
     * @return list<int>
     */
    public function findArchivedProductIds(): array;

    /**
     * @return list<Product>
     */
    public function listAllProducts(): array;

    /**
     * @return list<Product>
     */
    public function listProductsPage(int $offset, int $limit, ?string $q = null): array;

    public function countProducts(?string $q = null): int;

    /**
     * @param  list<string>|null  $ingredients
     * @param  array{calories?: float|int, proteins?: float|int, fats?: float|int, carbs?: float|int, basis?: string}|null  $nutrition
     */
    public function saveProduct(
        ?int $id,
        string $name,
        ?int $weight,
        string $status,
        ?string $description,
        ?string $sku,
        ?array $ingredients = null,
        ?array $nutrition = null,
        bool $isRoll = false,
    ): int;

    /**
     * @return list<array{id: int, url: string, path: string, sort_order: int, focus_x: float|null, focus_y: float|null}>
     */
    public function listItemImages(int $itemId): array;

    /**
     * Первое фото по каждому item_id (для списков/меню без N+1).
     *
     * @param  list<int>  $itemIds
     * @return array<int, array{url: string, focus_x: float|null, focus_y: float|null}>
     */
    public function findPrimaryImagesByItemIds(array $itemIds): array;

    /**
     * @return list<array{id: int, url: string, focus_x: float|null, focus_y: float|null}>
     */
    public function listProductImages(int $productId): array;

    public function addProductImage(int $productId, string $path): void;

    /**
     * @return array{id: int, url: string, path: string, sort_order: int, focus_x: float|null, focus_y: float|null}
     */
    public function addItemImage(int $itemId, string $path, ?float $focusX = null, ?float $focusY = null): array;

    /**
     * @return array{id: int, url: string, path: string, sort_order: int, focus_x: float|null, focus_y: float|null}
     */
    public function updateItemImage(
        int $itemId,
        int $imageId,
        ?float $focusX = null,
        ?float $focusY = null,
        ?int $sortOrder = null,
    ): array;

    public function deleteItemImage(int $itemId, int $imageId): void;

    public function deleteProduct(int $id): void;
}
