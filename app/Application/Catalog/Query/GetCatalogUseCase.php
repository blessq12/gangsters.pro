<?php

namespace App\Application\Catalog\Query;

use App\Application\Catalog\Presenter\CatalogPresenter;
use App\Domain\Supporting\Catalog\Entity\Category;
use App\Domain\Supporting\Catalog\Entity\CategoryItem;
use App\Domain\Supporting\Catalog\Entity\Product;
use App\Domain\Supporting\Catalog\Entity\ProductSet;
use App\Domain\Supporting\Catalog\Entity\Tag;
use App\Domain\Supporting\Catalog\Enum\CatalogItemKind;
use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;
use App\Domain\Supporting\Catalog\Repository\CategoryRepository;
use App\Domain\Supporting\Catalog\Repository\TagRepository;

/**
 * Дерево каталога для витрины: только активные категории и позиции.
 */
final class GetCatalogUseCase
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly CatalogItemRepository $catalogItems,
        private readonly TagRepository $tags,
        private readonly CatalogPresenter $presenter,
    ) {}

    /**
     * @return array{
     *     categories: list<array{category: array<string, mixed>, items: list<array<string, mixed>>}>,
     *     accompanying_categories: list<array{category: array<string, mixed>, items: list<array<string, mixed>>}>
     * }
     */
    public function execute(): array
    {
        $tagById = $this->indexTags($this->tags->findAllActiveOrdered());

        $menuCategories = [];
        $accompanyingCategories = [];

        foreach ($this->categories->findAllOrdered() as $category) {
            $node = $this->buildCategoryNode($category, $tagById);
            if ($node['items'] === []) {
                continue;
            }

            if ($category->isAccompanying()) {
                $accompanyingCategories[] = $node;
            } else {
                $menuCategories[] = $node;
            }
        }

        return [
            'categories' => $menuCategories,
            'accompanying_categories' => $accompanyingCategories,
        ];
    }

    /**
     * @param  array<int, Tag>  $tagById
     * @return array{category: array<string, mixed>, items: list<array<string, mixed>>}
     */
    private function buildCategoryNode(Category $category, array $tagById): array
    {
        $links = $this->categories->findItemsByCategoryId($category->id());

        $productIds = [];
        $setIds = [];
        foreach ($links as $link) {
            if ($link->kind() === CatalogItemKind::Set) {
                $setIds[] = $link->catalogItemId();
            } else {
                $productIds[] = $link->catalogItemId();
            }
        }

        $productsById = $this->indexProducts(
            $this->catalogItems->findActiveProductsByIds($productIds),
        );
        $setsById = $this->indexSets(
            $this->catalogItems->findActiveSetsByIds($setIds),
        );

        $lineProductIds = [];
        foreach ($setsById as $set) {
            foreach ($set->lines() as $line) {
                $lineProductIds[] = $line->productId();
            }
        }

        $uniqueLineProductIds = array_values(array_unique($lineProductIds));
        $lineProductNames = $this->catalogItems->findProductNamesByIds($uniqueLineProductIds);
        $lineProductsById = $this->indexProducts(
            $this->catalogItems->findActiveProductsByIds($uniqueLineProductIds),
        );

        $items = [];
        foreach ($links as $link) {
            $item = $this->resolveCategoryItem(
                $link,
                $productsById,
                $setsById,
                $tagById,
                $lineProductNames,
                $lineProductsById,
            );
            if ($item !== null) {
                $items[] = $item;
            }
        }

        return [
            'category' => $this->presenter->category($category),
            'items' => $items,
        ];
    }

    /**
     * @param  array<int, Product>  $productsById
     * @param  array<int, ProductSet>  $setsById
     * @param  array<int, Tag>  $tagById
     * @param  array<int, string>  $lineProductNames
     * @param  array<int, Product>  $lineProductsById
     * @return array<string, mixed>|null
     */
    private function resolveCategoryItem(
        CategoryItem $link,
        array $productsById,
        array $setsById,
        array $tagById,
        array $lineProductNames,
        array $lineProductsById,
    ): ?array {
        if ($link->kind() === CatalogItemKind::Set) {
            $set = $setsById[$link->catalogItemId()] ?? null;
            if (! $set instanceof ProductSet || $set->lines() === []) {
                return null;
            }

            return $this->presenter->productSet($set, $tagById, $lineProductNames, $lineProductsById);
        }

        $product = $productsById[$link->catalogItemId()] ?? null;

        return $product instanceof Product
            ? $this->presenter->product($product, $tagById)
            : null;
    }

    /**
     * @param  list<Tag>  $tags
     * @return array<int, Tag>
     */
    private function indexTags(array $tags): array
    {
        $indexed = [];
        foreach ($tags as $tag) {
            $indexed[$tag->id()] = $tag;
        }

        return $indexed;
    }

    /**
     * @param  list<Product>  $products
     * @return array<int, Product>
     */
    private function indexProducts(array $products): array
    {
        $indexed = [];
        foreach ($products as $product) {
            $indexed[$product->id()] = $product;
        }

        return $indexed;
    }

    /**
     * @param  list<ProductSet>  $sets
     * @return array<int, ProductSet>
     */
    private function indexSets(array $sets): array
    {
        $indexed = [];
        foreach ($sets as $set) {
            $indexed[$set->id()] = $set;
        }

        return $indexed;
    }
}
