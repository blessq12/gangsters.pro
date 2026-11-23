<?php

namespace App\Domain\Supporting\Catalog\Entity;

use App\Domain\Supporting\Catalog\ValueObject\TechCardLine;

/**
 * Технологическая карта товара каталога (1:1 к Product).
 */
final class TechCard
{
    /**
     * @param  list<TechCardLine>  $lines
     */
    private function __construct(
        private readonly int $productId,
        private readonly array $lines,
    ) {
        if ($this->productId < 1) {
            throw new \InvalidArgumentException('product_id техкарты должен быть положительным.');
        }
    }

    /**
     * @param  list<TechCardLine>  $lines
     */
    public static function forProduct(int $productId, array $lines): self
    {
        $normalized = [];
        foreach ($lines as $line) {
            if (! $line instanceof TechCardLine) {
                throw new \InvalidArgumentException('Строка техкарты должна быть TechCardLine.');
            }
            $normalized[] = $line;
        }

        return new self($productId, $normalized);
    }

    public function productId(): int
    {
        return $this->productId;
    }

    /**
     * @return list<TechCardLine>
     */
    public function lines(): array
    {
        return $this->lines;
    }

    public function isEmpty(): bool
    {
        return $this->lines === [];
    }
}
