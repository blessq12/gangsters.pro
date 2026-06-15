<?php

namespace App\Domain\Order\OrderDraft\ValueObject;

use App\Shared\ValueObject\Money;

final class CartSnapshot
{
    /**
     * @param  list<CartLineSnapshot>  $lines
     */
    private function __construct(
        private array $lines,
    ) {}

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @param  list<CartLineSnapshot>  $lines
     */
    public static function fromLines(array $lines): self
    {
        return new self(array_values($lines));
    }

    public function upsertLine(CartLineSnapshot $line): self
    {
        $lines = [];

        foreach ($this->lines as $existingLine) {
            if ($existingLine->matchesIdentity($line->productId(), $line->lineKind())) {
                continue;
            }

            $lines[] = $existingLine;
        }

        $lines[] = $line;

        return new self($lines);
    }

    public function removeLine(int $productId, string $lineKind = 'user'): self
    {
        $lines = array_values(array_filter(
            $this->lines,
            static fn (CartLineSnapshot $line): bool => ! $line->matchesIdentity($productId, $lineKind),
        ));

        return new self($lines);
    }

    /**
     * @return list<CartLineSnapshot>
     */
    public function lines(): array
    {
        return $this->lines;
    }

    public function hasItems(): bool
    {
        return $this->lines !== [];
    }

    public function itemsTotal(): Money
    {
        $total = Money::zero();

        foreach ($this->lines as $line) {
            $total = $total->add($line->lineTotal());
        }

        return $total;
    }

    public function payableTotal(): Money
    {
        $total = Money::zero();

        foreach ($this->lines as $line) {
            if ($line->isPromotionBenefitLine()) {
                continue;
            }

            $total = $total->add($line->lineTotal());
        }

        return $total;
    }
}
