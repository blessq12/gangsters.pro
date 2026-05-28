<?php

namespace App\Domain\Order\Entities;

use App\Domain\Order\Exceptions\OrderInvariantViolation;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\DeliveryInfo;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Domain\Order\ValueObjects\PaymentInfo;

class Order
{
    /**
     * @param  OrderItem[]  $items
     * @param  int  $subtotal  Копейки (RUB)
     * @param  int  $discountTotal  Копейки (RUB)
     * @param  int  $total  Копейки (RUB), товары после скидок + delivery_fee
     * @param  int  $deliveryFeeKopecks  Копейки (RUB)
     * @param  array<string, mixed>|null  $deliveryPricingSnapshot
     */
    public function __construct(
        private string $id,
        private ?int $clientId,
        private CustomerSnapshot $customer,
        private OrderStatus $status,
        private int $subtotal,
        private int $discountTotal,
        private int $total,
        private int $deliveryFeeKopecks,
        private ?DeliveryInfo $deliveryInfo,
        private ?PaymentInfo $paymentInfo,
        private array $items,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
        private ?array $deliveryPricingSnapshot = null,
    ) {
        $this->assertInvariant();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function getCustomer(): CustomerSnapshot
    {
        return $this->customer;
    }

    /**
     * @return OrderItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function getSubtotal(): int
    {
        return $this->subtotal;
    }

    public function getDiscountTotal(): int
    {
        return $this->discountTotal;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getDeliveryFeeKopecks(): int
    {
        return $this->deliveryFeeKopecks;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDeliveryPricingSnapshot(): ?array
    {
        return $this->deliveryPricingSnapshot;
    }

    /** Сумма товаров после скидок по строкам (без доставки). */
    public function getItemsNetTotalKopecks(): int
    {
        return $this->subtotal - $this->discountTotal;
    }

    public function getDeliveryInfo(): ?DeliveryInfo
    {
        return $this->deliveryInfo;
    }

    public function getPaymentInfo(): ?PaymentInfo
    {
        return $this->paymentInfo;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function addItem(OrderItem $item): void
    {
        $this->items[] = $item;
        $this->recalculateTotals();
        $this->assertInvariant();
    }

    public function setStatus(OrderStatus $status): void
    {
        $this->status = $status;
        $this->assertInvariant();
    }

    public function setPaymentInfo(PaymentInfo $paymentInfo): void
    {
        $this->paymentInfo = $paymentInfo;
        $this->assertInvariant();
    }

    public function markPreparing(): void
    {
        $this->status = OrderStatus::preparing();
        $this->assertInvariant();
    }

    public function markInTransit(): void
    {
        $this->status = OrderStatus::inTransit();
        $this->assertInvariant();
    }

    public function markDelivered(): void
    {
        $this->status = OrderStatus::delivered();
        $this->assertInvariant();
    }

    private function recalculateTotals(): void
    {
        $subtotal = 0;
        $discount = 0;

        foreach ($this->items as $item) {
            $subtotal += $item->getRowSubtotal();
            $discount += $item->getRowDiscount();
        }

        $this->subtotal = $subtotal;
        $this->discountTotal = $discount;
        $this->total = ($subtotal - $discount) + $this->deliveryFeeKopecks;
    }

    private function assertInvariant(): void
    {
        if ($this->subtotal < 0 || $this->discountTotal < 0 || $this->total < 0 || $this->deliveryFeeKopecks < 0) {
            throw new OrderInvariantViolation('Order monetary values must be non-negative.');
        }

        $itemsNet = $this->subtotal - $this->discountTotal;
        if ($this->total !== $itemsNet + $this->deliveryFeeKopecks) {
            throw new OrderInvariantViolation('Order totals are inconsistent.');
        }

        if (\count($this->items) === 0) {
            throw new OrderInvariantViolation('Order must contain at least one item.');
        }
    }
}
