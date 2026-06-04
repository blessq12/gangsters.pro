<?php

namespace App\Application\Order\Contracts;

interface OrderExternalLifecycleContract
{
    /**
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     * @param  array<string, mixed>  $deliveryAddress
     * @return array<string, mixed>
     */
    public function placeExternalOrder(
        ?int $clientId,
        string $customerName,
        string $customerPhone,
        ?string $customerEmail,
        string $deliveryMethod,
        array $deliveryAddress,
        ?string $deliveryComment,
        string $paymentMethod,
        string $paymentStatus,
        array $items,
    ): array;

    /**
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     * @param  array<string, mixed>  $deliveryAddress
     * @return array<string, mixed>|null
     */
    public function updateExternalOrder(
        string $orderId,
        ?int $clientId,
        string $customerName,
        string $customerPhone,
        ?string $customerEmail,
        string $deliveryMethod,
        array $deliveryAddress,
        ?string $deliveryComment,
        string $paymentMethod,
        string $paymentStatus,
        array $items,
    ): ?array;

    /**
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     * @return array<string, mixed>|null
     */
    public function updateOrderItems(string $orderId, array $items): ?array;

    public function cancelById(string $orderId): bool;
}
