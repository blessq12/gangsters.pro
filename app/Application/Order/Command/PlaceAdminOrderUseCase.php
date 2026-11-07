<?php

namespace App\Application\Order\Command;

use App\Application\Order\DTO\CreateOrderDto;
use App\Application\Order\DTO\PlaceAdminOrderDto;
use App\Domain\Catalog\Contract\CatalogItem;
use App\Domain\Catalog\Entity\Product;
use App\Domain\Catalog\Entity\ProductSet;
use App\Domain\Catalog\Repository\CatalogItemRepository;
use App\Domain\Order\Entity\Order;

final class PlaceAdminOrderUseCase
{
    public function __construct(
        private readonly CreateOrderUseCase $createOrder,
        private readonly CatalogItemRepository $items,
    ) {}

    public function execute(PlaceAdminOrderDto $input): Order
    {
        $clientName = trim($input->clientName);
        $phone = trim($input->phone);
        $deliveryMethod = $input->deliveryMethod === 'pickup' ? 'pickup' : 'courier';
        $address = trim($input->address);
        $paymentMethod = $input->paymentMethod;
        $cartLines = [];

        foreach ($input->lines as $line) {
            $productId = (int) ($line['product_id'] ?? 0);
            $qty = (int) ($line['qty'] ?? 0);
            if ($productId < 1 || $qty < 1) {
                continue;
            }

            $item = $this->items->findProductById($productId) ?? $this->items->findSetById($productId);
            if (! $item instanceof CatalogItem || ! $item->isActive()) {
                throw new \InvalidArgumentException('В чеке есть позиция, которой нет в продаже.');
            }

            $cartLines[] = [
                'product_id' => $item->id(),
                'product_name' => $item->name(),
                'quantity' => $qty,
                'line_total_rubles' => $this->priceRubles($item) * $qty,
                'payload' => ['kind' => 'user'],
            ];
        }

        if (
            $clientName === ''
            || $phone === ''
            || ($deliveryMethod === 'courier' && $address === '')
            || ! in_array($paymentMethod, ['cash', 'card_courier', 'card_online'], true)
            || $cartLines === []
        ) {
            throw new \InvalidArgumentException('Заказ заполнен не до конца.');
        }

        return $this->createOrder->execute(new CreateOrderDto(
            clientRequestId: 'admin-'.bin2hex(random_bytes(8)),
            cart: ['lines' => $cartLines],
            client: [
                'kind' => 'guest',
                'name' => $clientName,
                'phone' => $phone,
                'email' => null,
            ],
            delivery: [
                'method' => $deliveryMethod,
                'address' => $deliveryMethod === 'pickup' ? null : ['street' => $address],
                'comment' => null,
                'persons' => 1,
                'scheduled_at' => null,
            ],
            payment: [
                'method' => $paymentMethod,
                'change_from_rubles' => null,
            ],
            createdAt: $input->createdAt,
        ));
    }

    private function priceRubles(CatalogItem $item): int
    {
        if ($item instanceof Product || $item instanceof ProductSet) {
            return $item->price()->amountRubles();
        }

        throw new \InvalidArgumentException('В чеке есть позиция, которой нет в продаже.');
    }
}
