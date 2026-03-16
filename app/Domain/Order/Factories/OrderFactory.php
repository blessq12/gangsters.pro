<?php

namespace App\Domain\Order\Factories;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Domain\Order\ValueObjects\ProductSnapshot;

class OrderFactory
{
    /**
     * @param array<int, array{
     *     productOriginalId: int|null,
     *     name: string,
     *     sku: string,
     *     listPrice: int,
     *     finalPrice: int,
     *     quantity: int,
     *     attributes?: array,
     *     media?: array
     * }> $itemsData
     */
    public function create(
        string $id,
        int $clientId,
        CustomerSnapshot $customer,
        array $itemsData,
    ): Order {
        $items = [];

        foreach ($itemsData as $index => $row) {
            $productSnapshot = new ProductSnapshot(
                $row['name'],
                $row['sku'],
                $row['listPrice'],
                $row['finalPrice'],
                $row['attributes'] ?? [],
                $row['media'] ?? [],
            );

            $quantity = $row['quantity'];
            $unitPrice = $row['finalPrice'];
            $rowSubtotal = $unitPrice * $quantity;
            $rowDiscount = ($row['listPrice'] - $row['finalPrice']) * $quantity;
            $rowTotal = $rowSubtotal - $rowDiscount;

            $items[] = new OrderItem(
                id: (string) ($index + 1),
                orderId: $id,
                productOriginalId: $row['productOriginalId'],
                product: $productSnapshot,
                quantity: $quantity,
                unitPrice: $unitPrice,
                rowSubtotal: $rowSubtotal,
                rowDiscount: $rowDiscount,
                rowTotal: $rowTotal,
            );
        }

        $subtotal = 0;
        $discountTotal = 0;
        foreach ($items as $item) {
            $subtotal += $item->getRowSubtotal();
            $discountTotal += $item->getRowDiscount();
        }
        $total = $subtotal - $discountTotal;

        $createdAt = new \DateTimeImmutable();

        return new Order(
            id: $id,
            clientId: $clientId,
            customer: $customer,
            status: OrderStatus::new(),
            subtotal: $subtotal,
            discountTotal: $discountTotal,
            total: $total,
            deliveryInfo: null,
            paymentInfo: null,
            items: $items,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }
}

