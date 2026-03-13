<?php

namespace App\Domain\Product\Entity;

final class ProductIngredient
{
    private function __construct(
        private ?int $id,
        private string $name,
        private ?string $amount,
        private ?string $unit,
        private bool $isAllergen,
    ) {
    }

    public static function create(
        string $name,
        ?string $amount = null,
        ?string $unit = null,
        bool $isAllergen = false,
    ): self {
        return new self(
            id: null,
            name: $name,
            amount: $amount,
            unit: $unit,
            isAllergen: $isAllergen,
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function amount(): ?string
    {
        return $this->amount;
    }

    public function unit(): ?string
    {
        return $this->unit;
    }

    public function isAllergen(): bool
    {
        return $this->isAllergen;
    }
}

