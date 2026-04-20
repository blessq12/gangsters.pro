<?php

namespace App\Domain\Order\Factories;

use App\Domain\Order\Contracts\CatalogItemSnapshotProvider;
use App\Domain\Order\Exceptions\OrderItemNotFound;

final class OrderItemsFactory
{
    public function __construct(
        private readonly CatalogItemSnapshotProvider $catalogItems,
    ) {}

    /**
     * @param  array<int, array{product_id: int, quantity: int, final_price_kopecks?: int|null}>  $items
     * @return array<int, array{productOriginalId: int|null, name: string, sku: string, listPrice: int, finalPrice: int, quantity: int, attributes: array, media: array}> listPrice/finalPrice — копейки (RUB)
     */
    public function buildItemsData(array $items): array
    {
        $productIds = array_unique(array_column($items, 'product_id'));
        $productsById = $this->catalogItems->getActiveSnapshotsByIds($productIds);

        $result = [];

        foreach ($items as $row) {
            $productId = $row['product_id'];
            $quantity = $row['quantity'];

            $product = $productsById[$productId] ?? null;
            if ($product === null) {
                throw new OrderItemNotFound("Product not found: {$productId}");
            }

            $listPrice = (int) $product['price'];
            $finalPrice = $listPrice;
            if (array_key_exists('final_price_kopecks', $row) && is_int($row['final_price_kopecks'])) {
                $finalPrice = $row['final_price_kopecks'];
            }

            $result[] = [
                'productOriginalId' => $product['id'],
                'name' => $product['name'],
                'sku' => $product['sku'],
                'listPrice' => $listPrice,
                'finalPrice' => $finalPrice,
                'quantity' => $quantity,
                'attributes' => [],
                'media' => $product['media'],
            ];
        }

        return $result;
    }
}
