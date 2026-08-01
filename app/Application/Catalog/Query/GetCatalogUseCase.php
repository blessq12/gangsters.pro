<?php

namespace App\Application\Catalog\Query;

use App\Domain\Catalog\Entity\Category;
use App\Domain\Catalog\Entity\CategoryItem;
use App\Domain\Catalog\Entity\Product;
use App\Domain\Catalog\Entity\ProductSet;
use App\Domain\Catalog\Entity\Tag;
use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Domain\Catalog\Repository\CatalogItemRepository;
use App\Domain\Catalog\Repository\CategoryRepository;
use App\Domain\Catalog\Repository\TagRepository;
use App\Domain\Catalog\ValueObject\Nutrition;
use App\Domain\Catalog\ValueObject\ProductImage;
use App\Shared\ValueObject\Money;

/**
 * Сценарий: получить каталог для витрины.
 */
final class GetCatalogUseCase
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly CatalogItemRepository $catalogItems,
        private readonly TagRepository $tags,
    ) {}

    /**
     * @return array{
     *     categories: list<array{category: array<string, mixed>, items: list<array<string, mixed>>}>,
     *     accompanying_categories: list<array{category: array<string, mixed>, items: list<array<string, mixed>>}>,
     *     complement_products: list<array<string, mixed>>
     * }
     */
    public function execute(): array
    {
        return $this->buildCatalog(lite: false);
    }

    /**
     * Облегчённый каталог для critical bootstrap: без description/ingredients/nutrition, thumb-only images.
     *
     * @return array{
     *     categories: list<array{category: array<string, mixed>, items: list<array<string, mixed>>}>,
     *     accompanying_categories: list<array{category: array<string, mixed>, items: list<array<string, mixed>>}>,
     *     complement_products: list<array<string, mixed>>
     * }
     */
    public function executeLite(): array
    {
        return $this->buildCatalog(lite: true);
    }

    /**
     * @return array{
     *     categories: list<array{category: array<string, mixed>, items: list<array<string, mixed>>}>,
     *     accompanying_categories: list<array{category: array<string, mixed>, items: list<array<string, mixed>>}>,
     *     complement_products: list<array<string, mixed>>
     * }
     */
    private function buildCatalog(bool $lite): array
    {
        $tagById = $this->loadActiveTagsIndexed();

        $menuCategories = [];
        $accompanyingCategories = [];

        foreach ($this->categories->findAllOrdered() as $category) {
            $node = $this->buildCategoryNode($category, $tagById, $lite);
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
            'complement_products' => $this->buildComplementProducts($tagById, $lite),
        ];
    }

    /**
     * @param  array<int, Tag>  $tagById
     * @return list<array<string, mixed>>
     */
    private function buildComplementProducts(array $tagById, bool $lite): array
    {
        $products = $this->catalogItems->findActiveComplementSetProducts();
        if ($products === []) {
            return [];
        }

        $ids = array_map(static fn (Product $product): int => $product->id(), $products);
        $promotionMetaByProductId = $this->catalogItems->findPromotionMetaByProductIds($ids);

        $items = [];
        foreach ($products as $product) {
            $items[] = $this->mapProduct(
                $product,
                $tagById,
                $promotionMetaByProductId[$product->id()] ?? [
                    'counts_as_roll' => false,
                    'complement_set' => true,
                ],
                $lite,
            );
        }

        return $items;
    }

    /**
     * @param  array<int, Tag>  $tagById
     * @return array{category: array<string, mixed>, items: list<array<string, mixed>>}
     */
    private function buildCategoryNode(Category $category, array $tagById, bool $lite): array
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

        $lineProductNames = $this->catalogItems->findProductNamesByIds(
            array_values(array_unique($lineProductIds)),
        );

        $promotionMetaByProductId = $this->catalogItems->findPromotionMetaByProductIds($productIds);

        $items = [];

        foreach ($links as $link) {
            $item = $this->resolveCategoryItem(
                $link,
                $productsById,
                $setsById,
                $tagById,
                $lineProductNames,
                $promotionMetaByProductId,
                $lite,
            );
            if ($item !== null) {
                $items[] = $item;
            }
        }

        return [
            'category' => $this->mapCategory($category),
            'items' => $items,
        ];
    }

    /**
     * @param  array<int, Product>  $productsById
     * @param  array<int, ProductSet>  $setsById
     * @param  array<int, Tag>  $tagById
     * @param  array<int, string>  $lineProductNames
     * @return array<string, mixed>|null
     */
    private function resolveCategoryItem(
        CategoryItem $link,
        array $productsById,
        array $setsById,
        array $tagById,
        array $lineProductNames,
        array $promotionMetaByProductId,
        bool $lite,
    ): ?array {
        if ($link->kind() === CatalogItemKind::Set) {
            $set = $setsById[$link->catalogItemId()] ?? null;

            return $set instanceof ProductSet
                ? $this->mapProductSet($set, $tagById, $lineProductNames, $lite)
                : null;
        }

        $product = $productsById[$link->catalogItemId()] ?? null;

        if (! $product instanceof Product) {
            return null;
        }

        $promotionMeta = $promotionMetaByProductId[$product->id()] ?? null;
        if ((bool) ($promotionMeta['complement_set'] ?? false)) {
            // Комплектные товары отдаются отдельной группой, не в витрине категорий.
            return null;
        }

        return $this->mapProduct($product, $tagById, $promotionMeta, $lite);
    }

    /**
     * @return array<int, Tag>
     */
    private function loadActiveTagsIndexed(): array
    {
        $indexed = [];

        foreach ($this->tags->findAllActiveOrdered() as $tag) {
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

    /**
     * @return array<string, mixed>
     */
    private function mapCategory(Category $category): array
    {
        return [
            'id' => $category->id(),
            'name' => $category->name(),
            'slug' => $category->slug(),
            'sort_order' => $category->sortOrder(),
            'is_active' => $category->isActive(),
            'is_accompanying' => $category->isAccompanying(),
        ];
    }

    /**
     * @param  array<int, Tag>  $tagById
     * @param  array{counts_as_roll: bool, complement_set: bool}|null  $promotionMeta
     * @return array<string, mixed>
     */
    private function mapProduct(Product $product, array $tagById, ?array $promotionMeta, bool $lite): array
    {
        $payload = [
            'kind' => CatalogItemKind::Product->value,
            'id' => $product->id(),
            'name' => $product->name(),
            'slug' => $product->slug(),
            'status' => $product->status()->value,
            'price' => $this->mapMoney($product->price()),
            'tags' => $this->mapTags($product->tagIds(), $tagById),
            'images' => $this->mapImages($product->images(), $lite),
            'promotion_meta' => $this->mapPromotionMeta($promotionMeta),
        ];

        if ($lite) {
            return $payload;
        }

        return [
            ...$payload,
            'description' => $product->description(),
            'nutrition' => $this->mapNutrition($product->nutrition()),
            'ingredients' => $product->ingredients(),
        ];
    }

    /**
     * @param  array{counts_as_roll: bool, complement_set: bool}|null  $promotionMeta
     * @return array{counts_as_roll: bool, complement_set: bool}
     */
    private function mapPromotionMeta(?array $promotionMeta): array
    {
        return [
            'counts_as_roll' => (bool) ($promotionMeta['counts_as_roll'] ?? false),
            'complement_set' => (bool) ($promotionMeta['complement_set'] ?? false),
        ];
    }

    /**
     * @param  array<int, string>  $lineProductNames
     * @param  array<int, Tag>  $tagById
     * @return array<string, mixed>
     */
    private function mapProductSet(ProductSet $set, array $tagById, array $lineProductNames, bool $lite): array
    {
        $lines = [];

        foreach ($set->lines() as $line) {
            $productId = $line->productId();

            $lines[] = [
                'product_id' => $productId,
                'quantity' => $line->quantity(),
                'product_name' => $lineProductNames[$productId] ?? null,
            ];
        }

        $payload = [
            'kind' => CatalogItemKind::Set->value,
            'id' => $set->id(),
            'name' => $set->name(),
            'slug' => $set->slug(),
            'status' => $set->status()->value,
            'price' => $this->mapMoney($set->price()),
            'lines' => $lines,
            'tags' => $this->mapTags($set->tagIds(), $tagById),
            'images' => $this->mapImages($set->images(), $lite),
        ];

        if ($lite) {
            return $payload;
        }

        return [
            ...$payload,
            'description' => $set->description(),
        ];
    }

    /**
     * @param  list<int>  $tagIds
     * @param  array<int, Tag>  $tagById
     * @return list<array{code: string, label: string, color: string}>
     */
    private function mapTags(array $tagIds, array $tagById): array
    {
        $tags = [];

        foreach ($tagIds as $tagId) {
            $tag = $tagById[$tagId] ?? null;
            if (! $tag instanceof Tag) {
                continue;
            }

            $tags[] = [
                'code' => $tag->code(),
                'label' => $tag->label(),
                'color' => $tag->color(),
            ];
        }

        return $tags;
    }

    /**
     * @param  list<ProductImage>  $images
     * @return list<array{variants: list<array{size: string, path: string, width: int}>}>
     */
    private function mapImages(array $images, bool $lite = false): array
    {
        $mapped = [];

        foreach ($images as $image) {
            if (! $image instanceof ProductImage) {
                continue;
            }

            $path = $image->path();
            if ($path === '') {
                continue;
            }

            $mapped[] = [
                'variants' => $lite
                    ? [['size' => 'thumb', 'path' => $path, 'width' => 300]]
                    : [
                        ['size' => 'thumb', 'path' => $path, 'width' => 300],
                        ['size' => 'medium', 'path' => $path, 'width' => 800],
                        ['size' => 'large', 'path' => $path, 'width' => 1200],
                    ],
            ];
        }

        return $mapped;
    }

    /**
     * @return array{amount: int, currency: string}
     */
    private function mapMoney(Money $money): array
    {
        return [
            'amount' => $money->amountRubles(),
            'currency' => $money->currency(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function mapNutrition(?Nutrition $nutrition): ?array
    {
        if (! $nutrition instanceof Nutrition) {
            return null;
        }

        return [
            'calories' => $nutrition->calories(),
            'proteins' => $nutrition->proteins(),
            'fats' => $nutrition->fats(),
            'carbs' => $nutrition->carbs(),
            'basis' => $nutrition->basis(),
        ];
    }
}
