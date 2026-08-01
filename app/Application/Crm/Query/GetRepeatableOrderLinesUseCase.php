<?php

namespace App\Application\Crm\Query;

use App\Domain\Catalog\Repository\CatalogItemRepository;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Repository\OrderRepository;
use Illuminate\Auth\AuthenticationException;

final class GetRepeatableOrderLinesUseCase
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly CatalogItemRepository $catalogItems,
    ) {}

    /**
     * @return array{available_lines: list<array<string, mixed>>, unavailable_lines: list<array<string, mixed>>}
     */
    public function execute(int $clientId, int $orderId): array
    {
        $order = $this->orders->findById($orderId);
        if (
            ! $order instanceof Order
            || $order->clientId() !== $clientId
        ) {
            throw new AuthenticationException();
        }

        $items = [];
        foreach ($order->cart()['lines'] ?? [] as $line) {
            if (! is_array($line)) {
                continue;
            }

            $payload = is_array($line['payload'] ?? null) ? $line['payload'] : [];
            $kind = (string) ($payload['kind'] ?? 'user');
            if ($kind === 'gift' || $kind === 'complement') {
                continue;
            }

            $productId = (int) ($line['product_id'] ?? 0);
            $quantity = max(1, (int) ($line['quantity'] ?? 1));
            $unitPrice = (int) ($line['unit_price_rubles'] ?? 0);

            $items[] = [
                'product_id' => $productId,
                'product_name' => (string) ($line['product_name'] ?? ('Товар #'.$productId)),
                'quantity' => $quantity,
                'unit_price_rubles' => $unitPrice,
            ];
        }

        $productIds = array_values(array_unique(array_filter(
            array_map(static fn (array $item): int => $item['product_id'], $items),
            static fn (int $id): bool => $id > 0,
        )));

        $availableCatalog = [];
        foreach ($this->catalogItems->findActiveProductsByIds($productIds) as $product) {
            $availableCatalog[$product->id()] = $product;
        }
        foreach ($this->catalogItems->findActiveSetsByIds($productIds) as $set) {
            $availableCatalog[$set->id()] = $set;
        }

        $available = [];
        $unavailable = [];

        foreach ($items as $item) {
            $productId = $item['product_id'];
            $catalogItem = $availableCatalog[$productId] ?? null;
            $line = [
                ...$item,
                'catalog_kind' => $catalogItem !== null
                    ? $catalogItem->kind()->value
                    : 'product',
            ];

            if ($productId > 0 && $catalogItem !== null) {
                $available[] = $line;
            } else {
                $unavailable[] = $line;
            }
        }

        return [
            'available_lines' => $available,
            'unavailable_lines' => $unavailable,
        ];
    }
}
