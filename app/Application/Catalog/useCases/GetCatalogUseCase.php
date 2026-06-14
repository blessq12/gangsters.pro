<?php

namespace App\Application\Catalog\useCases;

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
     * @return array{categories: list<array{category: array<string, mixed>, items: list<array<string, mixed>>}>}
     */
    public function execute(): array
    {
        $tagById = $this->loadActiveTagsIndexed();

        $result = [];

        foreach ($this->categories->findAllOrdered() as $category) {
            $node = $this->buildCategoryNode($category, $tagById);

            if ($node['items'] !== []) {
                $result[] = $node;
            }
        }

        return ['categories' => $result];
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

        $lineProductNames = $this->catalogItems->findProductNamesByIds(
            array_values(array_unique($lineProductIds)),
        );

        $items = [];

        foreach ($links as $link) {
            $item = $this->resolveCategoryItem(
                $link,
                $productsById,
                $setsById,
                $tagById,
                $lineProductNames,
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
    ): ?array {
        if ($link->kind() === CatalogItemKind::Set) {
            $set = $setsById[$link->catalogItemId()] ?? null;

            return $set instanceof ProductSet
                ? $this->mapProductSet($set, $tagById, $lineProductNames)
                : null;
        }

        $product = $productsById[$link->catalogItemId()] ?? null;

        return $product instanceof Product
            ? $this->mapProduct($product, $tagById)
            : null;
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
        ];
    }

    /**
     * @param  array<int, Tag>  $tagById
     * @return array<string, mixed>
     */
    private function mapProduct(Product $product, array $tagById): array
    {
        return [
            'kind' => CatalogItemKind::Product->value,
            'id' => $product->id(),
            'name' => $product->name(),
            'slug' => $product->slug(),
            'status' => $product->status()->value,
            'price' => $this->mapMoney($product->price()),
            'description' => $product->description(),
            'nutrition' => $this->mapNutrition($product->nutrition()),
            'ingredients' => $product->ingredients(),
            'tags' => $this->mapTags($product->tagIds(), $tagById),
            'images' => $this->mapImages($product->images()),
        ];
    }

    /**
     * @param  array<int, string>  $lineProductNames
     * @param  array<int, Tag>  $tagById
     * @return array<string, mixed>
     */
    private function mapProductSet(ProductSet $set, array $tagById, array $lineProductNames): array
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

        return [
            'kind' => CatalogItemKind::Set->value,
            'id' => $set->id(),
            'name' => $set->name(),
            'slug' => $set->slug(),
            'status' => $set->status()->value,
            'price' => $this->mapMoney($set->price()),
            'description' => $set->description(),
            'lines' => $lines,
            'tags' => $this->mapTags($set->tagIds(), $tagById),
            'images' => $this->mapImages($set->images()),
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
    private function mapImages(array $images): array
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
                'variants' => [
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
