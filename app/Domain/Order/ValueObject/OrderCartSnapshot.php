<?php

namespace App\Domain\Order\ValueObject;

use App\Shared\ValueObject\Money;

final readonly class OrderCartSnapshot
{
    /**
     * @param  list<OrderLineSnapshot>  $lines
     */
    public function __construct(
        private array $lines,
    ) {}

    /**
     * @param  list<OrderLineSnapshot>  $lines
     */
    public static function fromLines(array $lines): self
    {
        return new self(array_values($lines));
    }

    /**
     * @return list<OrderLineSnapshot>
     */
    public function lines(): array
    {
        return $this->lines;
    }

    public function itemsTotal(): Money
    {
        $total = Money::zero();

        foreach ($this->lines as $line) {
            $total = $total->add($line->lineTotal());
        }

        return $total;
    }
}
