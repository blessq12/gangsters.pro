<?php

namespace App\Domain\Supporting\Catalog\Repository;

use App\Domain\Supporting\Catalog\Entity\Product;
use App\Domain\Supporting\Catalog\Entity\ProductSet;

interface CatalogItemRepository
{
    public function findProductById(int $id): ?Product;

    public function findSetById(int $id): ?ProductSet;

    /**
     * @param  list<int>  $ids
     * @return list<Product>
     */
    public function findActiveProductsByIds(array $ids): array;

    /**
     * @param  list<int>  $ids
     * @return list<ProductSet>
     */
    public function findActiveSetsByIds(array $ids): array;

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
     * @return list<ProductSet>
     */
    public function listAllSets(): array;

    /**
     * @return list<ProductSet>
     */
    public function listSetsPage(int $offset, int $limit, ?string $q = null): array;

    public function countSets(?string $q = null): int;

    /**
     * @param  list<array{type: string, amount: int}>  $prices  сумма в рублях
     * @param  list<string>|null  $ingredients
     * @param  array{calories?: float|int, proteins?: float|int, fats?: float|int, carbs?: float|int, basis?: string}|null  $nutrition
     * @param  list<int>|null  $tagIds
     */
    public function saveProduct(
        ?int $id,
        string $name,
        array $prices,
        ?int $weight,
        string $status,
        ?string $description,
        ?string $sku,
        ?array $ingredients = null,
        ?array $nutrition = null,
        ?array $tagIds = null,
    ): int;

    /**
     * @param  list<array{type: string, amount: int}>  $prices  сумма в рублях
     */
    public function saveSet(
        ?int $id,
        string $name,
        ?string $sku,
        array $prices,
        string $status,
        ?string $description = null,
    ): int;

    /**
     * @param  list<array{product_id: int, quantity: int}>  $lines
     */
    public function replaceSetLines(int $setId, array $lines): void;

    /**
     * @return list<array{id: int, url: string, path: string, sort_order: int, focus_x: float|null, focus_y: float|null}>
     */
    public function listItemImages(int $itemId): array;

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
}
