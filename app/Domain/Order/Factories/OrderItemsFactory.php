<?php

namespace App\Domain\Order\Factories;

use App\Domain\Order\Entities\OrderItem;
use App\Domain\Order\ValueObjects\ProductSnapshot;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Repository\ProductRepository;
use App\Domain\Product\VO\CustomerStatus as ProductCustomerStatus;
use LogicException;

final class OrderItemsFactory
{
    public function __construct(
        private readonly ProductRepository $products,
    ) {
    }

    /**
     * @param array<int, array{product_id: int, quantity: int}> $items
     * @return array<int, array{
     *     productOriginalId: int|null,
     *     name: string,
     *     sku: string,
     *     listPrice: int,
     *     finalPrice: int,
     *     quantity: int,
     *     attributes: array,
     *     media: array
     * }>
     */
    public function buildItemsData(array $items): array
    {
        $productIds = array_unique(array_column($items, 'product_id'));
        $products = $this->products->findByIds($productIds);

        $productsById = [];
        foreach ($products as $p) {
            $id = $p->id();
            if ($id !== null) {
                $productsById[$id] = $p;
            }
        }

        $customerStatus = new ProductCustomerStatus('regular');
        $result = [];

        foreach ($items as $row) {
            $productId = $row['product_id'];
            $quantity = $row['quantity'];

            /** @var Product|null $product */
            $product = $productsById[$productId] ?? null;
            if ($product === null) {
                throw new LogicException("Product not found: {$productId}");
            }
            if ($product->status() !== Product::STATUS_ACTIVE) {
                throw new LogicException("Product is not available: {$productId}");
            }

            $priceVO = $product->priceForStatus($customerStatus);
            if ($priceVO === null && \count($product->prices()) > 0) {
                $priceVO = $product->prices()[0];
            }
            if ($priceVO === null) {
                throw new LogicException("Product has no price: {$productId}");
            }

            $amount = $priceVO->amount();

            $result[] = [
                'productOriginalId' => $product->id(),
                'name' => $product->name(),
                'sku' => $product->articul() ?? (string) $product->id(),
                'listPrice' => $amount,
                'finalPrice' => $amount,
                'quantity' => $quantity,
                'attributes' => [],
                'media' => $this->productImagesToMedia($product->images()),
            ];
        }

        return $result;
    }

    /**
     * @param \App\Domain\Product\Entity\ProductImage[] $images
     * @return array<int, array<string, mixed>>
     */
    private function productImagesToMedia(array $images): array
    {
        $out = [];
        foreach ($images as $img) {
            foreach ($img->variants() as $v) {
                $out[] = [
                    'url' => $v->path(),
                    'variant' => $v->size(),
                ];
            }
        }

        return $out;
    }
}

