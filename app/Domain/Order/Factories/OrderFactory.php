<?php

namespace App\Domain\Order\Factories;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;
use App\Domain\Order\Services\OrderIdGenerator;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\DeliveryInfo;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Domain\Order\ValueObjects\PaymentInfo;
use App\Domain\Order\ValueObjects\ProductSnapshot;

class OrderFactory
{
    public function __construct(
        private readonly OrderIdGenerator $idGenerator,
    ) {}

    /**
     * @param  array<int, array{productOriginalId: int|null, name: string, sku: string, listPrice: int, finalPrice: int, quantity: int, attributes?: array, media?: array}>  $itemsData  listPrice/finalPrice — копейки (RUB)
     */
    public function create(
        ?int $clientId,
        CustomerSnapshot $customer,
        array $itemsData,
        ?DeliveryInfo $deliveryInfo = null,
        ?PaymentInfo $paymentInfo = null,
    ): Order {
        $items = [];

        $id = $this->idGenerator->generate();

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
            $unitPrice = $row['listPrice'];
            $rowSubtotal = $unitPrice * $quantity;
            $rowDiscount = ($row['listPrice'] - $row['finalPrice']) * $quantity;
            $rowTotal = $row['finalPrice'] * $quantity;

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

        $createdAt = new \DateTimeImmutable;

        return new Order(
            id: $id,
            clientId: $clientId,
            customer: $customer,
            status: OrderStatus::new(),
            subtotal: $subtotal,
            discountTotal: $discountTotal,
            total: $total,
            deliveryInfo: $deliveryInfo,
            paymentInfo: $paymentInfo,
            items: $items,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );
    }

    /**
     * Пересборка заказа с сохранением id и даты создания (обновление состава/шапки).
     *
     * @param  array<int, array{productOriginalId: int|null, name: string, sku: string, listPrice: int, finalPrice: int, quantity: int, attributes?: array, media?: array}>  $itemsData  listPrice/finalPrice — копейки (RUB)
     */
    public function rebuildOrder(
        string $id,
        ?int $clientId,
        CustomerSnapshot $customer,
        OrderStatus $status,
        array $itemsData,
        ?DeliveryInfo $deliveryInfo,
        ?PaymentInfo $paymentInfo,
        \DateTimeImmutable $createdAt,
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
            $unitPrice = $row['listPrice'];
            $rowSubtotal = $unitPrice * $quantity;
            $rowDiscount = ($row['listPrice'] - $row['finalPrice']) * $quantity;
            $rowTotal = $row['finalPrice'] * $quantity;

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

        $updatedAt = new \DateTimeImmutable;

        return new Order(
            id: $id,
            clientId: $clientId,
            customer: $customer,
            status: $status,
            subtotal: $subtotal,
            discountTotal: $discountTotal,
            total: $total,
            deliveryInfo: $deliveryInfo,
            paymentInfo: $paymentInfo,
            items: $items,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
