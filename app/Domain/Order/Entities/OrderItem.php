<?php

namespace App\Domain\Order\Entities;

use App\Domain\Order\Exceptions\OrderInvariantViolation;
use App\Domain\Order\ValueObjects\ProductSnapshot;

class OrderItem
{
    public function __construct(
        private string $id,
        private string $orderId,
        private ?int $productOriginalId,
        private ProductSnapshot $product,
        private int $quantity,
        private int $unitPrice,
        private int $rowSubtotal,
        private int $rowDiscount,
        private int $rowTotal,
    ) {
        $this->assertInvariant();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getProductOriginalId(): ?int
    {
        return $this->productOriginalId;
    }

    public function getProduct(): ProductSnapshot
    {
        return $this->product;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitPrice(): int
    {
        return $this->unitPrice;
    }

    public function getRowSubtotal(): int
    {
        return $this->rowSubtotal;
    }

    public function getRowDiscount(): int
    {
        return $this->rowDiscount;
    }

    public function getRowTotal(): int
    {
        return $this->rowTotal;
    }

    private function assertInvariant(): void
    {
        if ($this->quantity <= 0) {
            throw new OrderInvariantViolation('Order item quantity must be greater than zero.');
        }

        if ($this->unitPrice < 0 || $this->rowSubtotal < 0 || $this->rowDiscount < 0 || $this->rowTotal < 0) {
            throw new OrderInvariantViolation('Order item monetary values must be non-negative.');
        }

        if ($this->rowSubtotal !== $this->unitPrice * $this->quantity) {
            throw new OrderInvariantViolation('Order item subtotal is inconsistent.');
        }

        if ($this->rowTotal !== $this->rowSubtotal - $this->rowDiscount) {
            throw new OrderInvariantViolation('Order item total is inconsistent.');
        }
    }
}
