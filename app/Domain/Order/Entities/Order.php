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
     * @param  int  $total  Копейки (RUB)
     */
    public function __construct(
        private string $id,
        private ?int $clientId,
        private CustomerSnapshot $customer,
        private OrderStatus $status,
        private int $subtotal,
        private int $discountTotal,
        private int $total,
        private ?DeliveryInfo $deliveryInfo,
        private ?PaymentInfo $paymentInfo,
        private array $items,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
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
        $this->total = $subtotal - $discount;
    }

    private function assertInvariant(): void
    {
        if ($this->subtotal < 0 || $this->discountTotal < 0 || $this->total < 0) {
            throw new OrderInvariantViolation('Order monetary values must be non-negative.');
        }

        if ($this->total !== $this->subtotal - $this->discountTotal) {
            throw new OrderInvariantViolation('Order totals are inconsistent.');
        }

        if (\count($this->items) === 0) {
            throw new OrderInvariantViolation('Order must contain at least one item.');
        }
    }
}
