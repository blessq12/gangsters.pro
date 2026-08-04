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
     * (meta_is_complement_set), даже если is_system — их можно докупить.
     *
     * @param  list<int>  $ids
     * @return list<Product>
     */
    public function findActiveProductsByIds(array $ids): array;

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
}
