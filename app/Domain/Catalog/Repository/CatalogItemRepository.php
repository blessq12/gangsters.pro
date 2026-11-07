<?php

namespace App\Domain\Catalog\Repository;

use App\Domain\Catalog\Entity\Product;
use App\Domain\Catalog\Entity\ProductSet;

interface CatalogItemRepository
{
    public function findProductById(int $id): ?Product;

    public function findSetById(int $id): ?ProductSet;

    /**
     * Активные товары для корзины/витрины: не-system, плюс комплектные
     * (meta_is_complement_set) и их платные близнецы (paid_twin), даже если is_system.
     *
     * @param  list<int>  $ids
     * @return list<Product>
     */
    public function findActiveProductsByIds(array $ids): array;

    /**
     * Id товаров, на которые ссылаются комплектные как на платного близнеца.
     *
     * @return list<int>
     */
    public function findPaidTwinProductIds(): array;

    /**
     * Активные системные товары (кандидаты подарка).
     *
     * @return list<Product>
     */
    public function findActiveSystemProducts(): array;

    /**
     * Активные товары-наборы дополнений (meta_is_complement_set).
     *
     * @return list<Product>
     */
    public function findActiveComplementSetProducts(): array;

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
     * @param  list<int>  $ids
     * @return array<int, array{counts_as_roll: bool, complement_set: bool}>
     */
    public function findPromotionMetaByProductIds(array $ids): array;

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
        ?int $oldPrice,
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
        ?int $oldPrice,
        string $status,
    ): int;

    /**
     * @return list<array{id: int, url: string, focus_x: float|null, focus_y: float|null}>
     */
    public function listProductImages(int $productId): array;

    public function addProductImage(int $productId, string $path): void;
}
