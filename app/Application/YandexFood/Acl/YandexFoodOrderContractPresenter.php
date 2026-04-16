<?php

namespace App\Application\YandexFood\Acl;

use App\Application\YandexFood\Contracts\YandexFoodOrderMetaStore;

/**
 * ACL: order read-model → тело ответа в форме контракта JSON API Яндекс Еда.
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
    public function presentCreateSuccess(array $order): array
    {
        return [
            'result' => 'OK',
            'orderId' => (string) ($order['id'] ?? ''),
        ];
    }

    /**
     * Успешная выдача заказа по id (result + order).
     *
     * @return array{result: string, order: array<string, mixed>}
     */
    public function presentGetByIdSuccess(array $order): array
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
    public function presentOrderStatus(array $order): array
    {
        return [
            'status' => $this->mapDomainStatusToYandex($order),
            'comment' => '',
            'updatedAt' => (string) ($order['updated_at'] ?? ''),
        ];
    }

    /**
     * Успешное обновление заказа (result + orderId).
     *
     * @return array{result: string, orderId: string}
     */
    public function presentUpdateSuccess(array $order): array
    {
        return [
            'result' => 'OK',
            'orderId' => (string) ($order['id'] ?? ''),
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
    public function integrationMetaByOrderId(string $orderId): ?array
    {
        return $this->metaStore->findByOrderId($orderId);
    }

    /**
     * @return array<string, mixed>
     */
    private function presentOrderPayload(array $order): array
    {
        $delivery = is_array($order['delivery'] ?? null) ? $order['delivery'] : [];
        $addr = is_array($delivery['address'] ?? null) ? $delivery['address'] : [];
        $comment = $delivery['comment'] ?? null;
        $orderId = (string) ($order['id'] ?? '');
        $meta = $orderId !== '' ? ($this->metaStore->findByOrderId($orderId) ?? []) : [];
        $payment = $meta['yandex_payment'] ?? [];

        $itemsCost = $payment['itemsCost'] ?? ($order['subtotal'] ?? 0);
        $deliveryFee = $payment['deliveryFee'] ?? 0;
        $total = $payment['total'] ?? ($order['total'] ?? 0);
        $change = $payment['change'] ?? 0;
        $customer = is_array($order['customer'] ?? null) ? $order['customer'] : [];
        $items = is_array($order['items'] ?? null) ? $order['items'] : [];

        return [
            'id' => $orderId,
            'name' => (string) ($customer['name'] ?? ''),
            'tel' => (string) ($customer['phone'] ?? ''),
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
            'discriminator' => (string) ($delivery['method'] ?? ''),
            'items' => array_map(
                fn (array $item) => $this->presentOrderLine($item),
                $items,
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentOrderLine(array $item): array
    {
        $product = is_array($item['product'] ?? null) ? $item['product'] : [];
        $productId = $item['product_original_id'] ?? null;

        return [
            'id' => $productId !== null ? (string) $productId : (string) ($item['id'] ?? ''),
            'name' => (string) ($product['name'] ?? ''),
            'quantity' => (int) ($item['quantity'] ?? 0),
            'price' => (float) ($item['unit_price'] ?? 0),
            'modifications' => [],
            'promos' => [],
        ];
    }

    private function mapDomainStatusToYandex(array $order): string
    {
        return match ((string) ($order['status'] ?? '')) {
            'new' => 'NEW',
            'preparing', 'in_transit', 'delivered' => 'PAID',
            default => 'NEW',
        };
    }

}
