<?php

namespace App\Application\YandexFood\Acl;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;

/**
 * ACL: доменный {@see Order} → тело ответа как у {@see \App\Services\Yandex\YandexFoodOrderService}
 * / {@see \App\Http\Controllers\Api\YandexFoodController} (JSON-ключи и форма, без JsonResponse).
 */
final class YandexFoodOrderContractPresenter
{
    /**
     * @see \App\Services\Yandex\YandexFoodOrderService::createOrder() успех
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
     * @see \App\Services\Yandex\YandexFoodOrderService::getOrderById() успех
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
     * @see \App\Services\Yandex\YandexFoodOrderService::getOrderStatus()
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
     * @see \App\Services\Yandex\YandexFoodOrderService::updateOrder() успех
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
     * @see \App\Services\Yandex\YandexFoodOrderService::deleteOrder() успех
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
        return $this->extractYandexMeta($order->getDeliveryInfo()?->comment);
    }

    /**
     * @return array<string, mixed>
     */
    private function presentOrderPayload(Order $order): array
    {
        $delivery = $order->getDeliveryInfo();
        $addr = $delivery?->address ?? [];
        $comment = $delivery?->comment;
        $meta = $this->extractYandexMeta($comment);
        $payment = $meta['yandex_payment'] ?? [];

        $itemsCost = $payment['itemsCost'] ?? $this->kopecksToRublesFloat($order->getSubtotal());
        $deliveryFee = $payment['deliveryFee'] ?? 0;
        $total = $payment['total'] ?? $this->kopecksToRublesFloat($order->getTotal());
        $change = $payment['change'] ?? 0;

        return [
            'id' => $order->getId(),
            'name' => $order->getCustomer()->name,
            'tel' => $order->getCustomer()->phone,
            'full_address' => $addr['full'] ?? '',
            'restaurantId' => $meta['yandex_restaurant_id'] ?? null,
            'personQty' => $meta['yandex_persons'] ?? null,
            'comment' => $this->stripYandexMetaBlock($comment),
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
            'price' => $this->kopecksToRublesFloat($item->getUnitPrice()),
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

    private function kopecksToRublesFloat(int $kopecks): float
    {
        return round($kopecks / 100, 2);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function extractYandexMeta(?string $comment): ?array
    {
        if ($comment === null || $comment === '') {
            return null;
        }

        $marker = '[yandex_meta]';
        $pos = strrpos($comment, $marker);
        if ($pos === false) {
            return null;
        }

        $json = trim(substr($comment, $pos + strlen($marker)));
        if ($json === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function stripYandexMetaBlock(?string $comment): string
    {
        if ($comment === null || $comment === '') {
            return '';
        }

        $marker = '[yandex_meta]';
        $pos = strrpos($comment, $marker);
        if ($pos === false) {
            return $comment;
        }

        return rtrim(substr($comment, 0, $pos));
    }
}
