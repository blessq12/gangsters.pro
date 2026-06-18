<?php

namespace App\Domain\Promotion\ValueObject;

/**
 * Товар-кандидат для расчёта benefit (подарок / комплект).
 */
final readonly class BenefitProductCandidate
{
    /**
     * @param  list<string>  $composition
     */
    public function __construct(
        private int $productId,
        private string $productName,
        private int $priceRubles,
        private ?string $imageUrl,
        private array $composition = [],
    ) {}

    public function productId(): int
    {
        return $this->productId;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function priceRubles(): int
    {
        return $this->priceRubles;
    }

    public function imageUrl(): ?string
    {
        return $this->imageUrl;
    }

    /**
     * @return list<string>
     */
    public function composition(): array
    {
        return $this->composition;
    }
}
