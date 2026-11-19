<?php

namespace App\Domain\Supporting\Catalog\Repository;

use App\Domain\Supporting\Catalog\Entity\Product;
use App\Domain\Supporting\Catalog\Entity\ProductSet;

interface CatalogItemRepository
{
    public function findProductById(int $id): ?Product;

    public function findSetById(int $id): ?ProductSet;

    /**
     * Активные не архивные товары по статусу.
     *
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
     * Архивные товары — для выгрузки недоступных позиций во внешние меню.
     *
     * @return list<int>
     */
    public function findArchivedProductIds(): array;

    /**
     * @return list<Product>
     */
    public function listAllProducts(): array;

    /**
     * @return list<ProductSet>
     */
    public function listAllSets(): array;

    public function saveProduct(
        ?int $id,
        string $name,
        int $price,
        ?int $weight,
        string $status,
        ?string $description,
        ?string $sku,
    ): int;

    public function saveSet(
        ?int $id,
        string $name,
        ?string $sku,
        int $price,
        string $status,
    ): int;

    /**
     * @return list<array{id: int, url: string, focus_x: float|null, focus_y: float|null}>
     */
    public function listProductImages(int $productId): array;

    public function addProductImage(int $productId, string $path): void;
}
