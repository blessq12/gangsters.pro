<?php

namespace App\Application\YandexFood\Query;

use App\Application\YandexFood\DTO\YandexMenuCompositionRequestDto;
use App\Application\YandexFood\Presenter\YandexFoodMenuCatalogPresenter;
use App\Application\YandexFood\YandexFoodBaseUseCase;
use App\Domain\Category\Entity\Category;
use App\Domain\Category\Entity\CategoryProduct;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Repository\ProductRepository;

final class GetYandexFoodMenuCompositionUseCase extends YandexFoodBaseUseCase
{
    public function __construct(
        OrderRepositoryInterface $orders,
        ProductRepository $products,
        CategoryRepository $categories,
        private readonly YandexFoodMenuCatalogPresenter $yandexMenuCatalog,
    ) {
        parent::__construct($orders, $products, $categories);
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(YandexMenuCompositionRequestDto $dto): array
    {
        $blocks = [];

        foreach ($this->categories->findAllOrdered() as $category) {
            if (!$category->isActive()) {
                continue;
            }

            $block = $this->buildCategoryBlock($category);
            if ($block !== null) {
                $blocks[] = $block;
            }
        }

        return $this->yandexMenuCatalog->presentMenuComposition($blocks);
    }

    /**
     * @return array{category: Category, lines: list<array{product: Product, sortOrder: int}>}|null
     */
    private function buildCategoryBlock(Category $category): ?array
    {
        $links = $this->categories->findLinksByCategoryId($category->id());

        usort(
            $links,
            static fn (CategoryProduct $a, CategoryProduct $b) => $a->sortOrder() <=> $b->sortOrder(),
        );

        $productIds = array_map(
            static fn (CategoryProduct $link) => $link->productId(),
            $links,
        );

        if ($productIds === []) {
            return null;
        }

        $products = $this->products->findByIds($productIds);

        $productsById = [];
        foreach ($products as $product) {
            if ($product instanceof Product && $product->status() === Product::STATUS_ACTIVE) {
                $productsById[$product->id()] = $product;
            }
        }

        $status = YandexFoodMenuCatalogPresenter::priceCustomerStatus();
        $lines = [];

        foreach ($links as $link) {
            $id = $link->productId();
            if (!isset($productsById[$id])) {
                continue;
            }

            $product = $productsById[$id];
            $price = $product->priceForStatus($status);
            if ($price === null || $price->amount() <= 0) {
                continue;
            }

            $lines[] = [
                'product' => $product,
                'sortOrder' => $link->sortOrder(),
            ];
        }

        if ($lines === []) {
            return null;
        }

        return [
            'category' => $category,
            'lines' => $lines,
        ];
    }
}
