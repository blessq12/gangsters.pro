<?php

namespace App\Application\Catalog\Query;

use App\Domain\Catalog\Entity\Product;
use App\Domain\Catalog\Entity\ProductSet;
use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Domain\Catalog\Repository\CatalogItemRepository;
use App\Domain\Catalog\Repository\CategoryRepository;
use App\Domain\Catalog\Repository\TagRepository;

final class GetAdminCatalogUseCase
{
    public function __construct(
        private readonly CategoryRepository $categories,
        private readonly CatalogItemRepository $items,
        private readonly TagRepository $tags,
    ) {}

    /**
     * @return array{
     *     categories: list<array<string, mixed>>,
     *     products: list<array<string, mixed>>,
     *     sets: list<array<string, mixed>>,
     *     tags: list<array<string, mixed>>
     * }
     */
    public function execute(): array
    {
        $categoryRows = [];
        $categoryIdByProduct = [];

        foreach ($this->categories->findAllIncludingInactive() as $category) {
            $productIds = [];
            foreach ($this->categories->findItemsByCategoryId($category->id()) as $item) {
                if ($item->kind() !== CatalogItemKind::Product) {
                    continue;
                }
                $productIds[] = $item->catalogItemId();
                $categoryIdByProduct[$item->catalogItemId()] ??= $category->id();
            }

            $categoryRows[] = [
                'id' => $category->id(),
                'name' => $category->name(),
                'is_active' => $category->isActive(),
                'is_accompanying' => $category->isAccompanying(),
                'product_ids' => $productIds,
            ];
        }

        $products = [];
        foreach ($this->items->listAllProducts() as $product) {
            $products[] = $this->presentProduct($product, $categoryIdByProduct[$product->id()] ?? null);
        }

        $sets = [];
        foreach ($this->items->listAllSets() as $set) {
            $sets[] = $this->presentSet($set);
        }

        $tags = [];
        foreach ($this->tags->findAllOrdered() as $tag) {
            $tags[] = [
                'id' => $tag->id(),
                'code' => $tag->code(),
                'label' => $tag->label(),
                'color' => $tag->color(),
                'is_active' => $tag->isActive(),
            ];
        }

        return [
            'categories' => $categoryRows,
            'products' => $products,
            'sets' => $sets,
            'tags' => $tags,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentProduct(Product $product, ?int $categoryId): array
    {
        return [
            'id' => $product->id(),
            'name' => $product->name(),
            'category_id' => $categoryId,
            'price' => $product->price()->amountRubles(),
            'old_price' => $product->oldPrice()?->amountRubles(),
            'weight' => $product->weight(),
            'status' => $product->status()->value,
            'is_system' => $product->isSystem(),
            'description' => $product->description(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentSet(ProductSet $set): array
    {
        return [
            'id' => $set->id(),
            'name' => $set->name(),
            'slug' => $set->slug(),
            'sku' => $set->sku(),
            'line_count' => count($set->lines()),
            'price' => $set->price()->amountRubles(),
            'old_price' => $set->oldPrice()?->amountRubles(),
            'status' => $set->status()->value,
        ];
    }
}
