<?php

namespace App\Application\Catalog\DTO;

final readonly class AdminIngredientDTO
{
    public function __construct(
        public string $name,
        public ?string $amount = null,
        public ?string $unit = null,
        public bool $isAllergen = false,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            amount: isset($data['amount']) ? (string) $data['amount'] : null,
            unit: isset($data['unit']) ? (string) $data['unit'] : null,
            isAllergen: (bool) ($data['is_allergen'] ?? false),
        );
    }
}
