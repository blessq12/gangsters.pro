<?php

namespace App\Domain\Core\Order\ValueObject;

use App\Domain\Core\Order\Enum\OrderLineType;

/**
 * Позиция заказа: снимок товара на момент оформления.
 */
final readonly class OrderLine
{
    public function __construct(
        private int $productId,
        private string $name,
        private int $unitPriceKopecks,
        private int $quantity,
        private OrderLineType $lineType,
    ) {
        if ($this->productId < 1) {
            throw new \InvalidArgumentException('productId должен быть положительным.');
        }

        if (trim($this->name) === '') {
            throw new \InvalidArgumentException('Название позиции обязательно.');
        }

        if ($this->unitPriceKopecks < 0) {
            throw new \InvalidArgumentException('Цена позиции не может быть отрицательной.');
        }

        if ($this->quantity < 1) {
            throw new \InvalidArgumentException('Количество позиции должно быть не меньше 1.');
        }

        if (in_array($this->lineType, [OrderLineType::Gift, OrderLineType::Complement], true)
            && $this->unitPriceKopecks !== 0
        ) {
            throw new \InvalidArgumentException('Подарок и комплект идут по нулевой цене.');
        }
    }

    public function productId(): int
    {
        return $this->productId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function unitPriceKopecks(): int
    {
        return $this->unitPriceKopecks;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function lineType(): OrderLineType
    {
        return $this->lineType;
    }

    public function lineTotalKopecks(): int
    {
        return $this->unitPriceKopecks * $this->quantity;
    }

    public function isBillable(): bool
    {
        return in_array($this->lineType, [OrderLineType::Regular, OrderLineType::Set], true);
    }
}
