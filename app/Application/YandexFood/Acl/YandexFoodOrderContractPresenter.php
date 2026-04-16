<?php

namespace App\Application\YandexFood\Acl;

use App\Application\YandexFood\Contracts\YandexFoodOrderMetaStore;
use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;
use App\Support\Money;

/**
 * ACL: доменный {@see Order} → тело ответа в форме контракта JSON API Яндекс Еда (как было в легаси-слое).
 */
final class YandexFoodOrderContractPresenter
{
    public function __construct(
        private readonly YandexFoodOrderMetaStore $metaStore,
    ) {
    }

    /**
     * Успешное создание заказа (result + orderId).
     *
     * @return array{result: string, orderId: string}
     */
    public function presentCreateSuccess(Order $order): array
    {
        return [
            'result' => 'OK',
            'orderId' => $order->getId(),
        ];
    }

    /**
     * Успешная выдача заказа по id (result + order).
     *
     * @return array{result: string, order: array<string, mixed>}
     */
    public function presentGetByIdSuccess(Order $order): array
    {
        return [
            'result' => 'OK',
            'order' => $this->presentOrderPayload($order),
        ];
    }

    /**
     * Статус заказа для внешнего API (status, comment, updatedAt).
     *
     * @return array{status: string, comment: string, updatedAt: string}
     */
    public function presentOrderStatus(Order $order): array
    {
        return [
            'status' => $this->mapDomainStatusToYandex($order),
            'comment' => '',
            'updatedAt' => $order->getUpdatedAt()->format(DATE_ATOM),
        ];
    }

    /**
     * Успешное обновление заказа (result + orderId).
     *
     * @return array{result: string, orderId: string}
     */
    public function presentUpdateSuccess(Order $order): array
    {
        return [
            'result' => 'OK',
            'orderId' => $order->getId(),
        ];
    }

    /**
     * Успешное удаление заказа (result + orderId).
     *
     * @return array{result: string, orderId: string}
     */
    public function presentDeleteSuccess(string $orderId): array
    {
        return [
            'result' => 'OK',
            'orderId' => $orderId,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function integrationMeta(Order $order): ?array
    {
        return $this->metaStore->findByOrderId($order->getId());
    }

    /**
     * @return array<string, mixed>
     */
    private function presentOrderPayload(Order $order): array
    {
        $delivery = $order->getDeliveryInfo();
        $addr = $delivery?->address ?? [];
        $comment = $delivery?->comment;
        $meta = $this->metaStore->findByOrderId($order->getId()) ?? [];
        $payment = $meta['yandex_payment'] ?? [];

        $itemsCost = $payment['itemsCost'] ?? Money::kopecksToApiRubles($order->getSubtotal());
        $deliveryFee = $payment['deliveryFee'] ?? 0;
        $total = $payment['total'] ?? Money::kopecksToApiRubles($order->getTotal());
        $change = $payment['change'] ?? 0;

        return [
            'id' => $order->getId(),
            'name' => $order->getCustomer()->name,
            'tel' => $order->getCustomer()->phone,
            'full_address' => $addr['full'] ?? '',
            'restaurantId' => $meta['yandex_restaurant_id'] ?? null,
            'personQty' => $meta['yandex_persons'] ?? null,
            'comment' => $comment ?? '',
            'latitude' => $addr['latitude'] ?? null,
            'longitude' => $addr['longitude'] ?? null,
            'deliveryDate' => $addr['delivery_at'] ?? null,
            'deliveryType' => $delivery?->method ?? '',
            'itemsCost' => $itemsCost,
            'deliveryFee' => $deliveryFee,
            'total' => $total,
            'change' => $change,
            'promos' => $meta['yandex_promos'] ?? [],
            'discriminator' => $delivery?->method ?? '',
            'items' => array_map(
                fn (OrderItem $item) => $this->presentOrderLine($item),
                $order->getItems(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentOrderLine(OrderItem $item): array
    {
        $product = $item->getProduct();
        $productId = $item->getProductOriginalId();

        return [
            'id' => $productId !== null ? (string) $productId : $item->getId(),
            'name' => $product->name,
            'quantity' => $item->getQuantity(),
            'price' => Money::kopecksToApiRubles($item->getUnitPrice()),
            'modifications' => [],
            'promos' => [],
        ];
    }

    private function mapDomainStatusToYandex(Order $order): string
    {
        return match ($order->getStatus()->value) {
            'new' => 'NEW',
            'preparing', 'in_transit', 'delivered' => 'PAID',
            default => 'NEW',
        };
    }

}
