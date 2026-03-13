<?php

namespace App\Domain\Product\Entity;

use App\Domain\Product\VO\Nutrition;
use App\Domain\Product\VO\ProductTag;
use App\Domain\Product\VO\Price;
use DateTimeImmutable;

final class Product
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';

    /**
     * @param ProductImage[]      $images
     * @param ProductIngredient[] $ingredients
     * @param ProductTag[]        $tags
     * @param Price[]             $prices
     */
    private function __construct(
        private ?int $id,
        private string $name,
        private string $description,
        private Nutrition $nutrition,
        private array $images,
        private array $ingredients,
        private array $tags,
        private array $prices,
        private string $status,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
        private ?DateTimeImmutable $archivedAt,
    ) {
    }

    /**
     * @param ProductImage[]      $images
     * @param ProductIngredient[] $ingredients
     * @param ProductTag[]        $tags
     * @param Price[]             $prices
     */
    public static function create(
        string $name,
        string $description,
        Nutrition $nutrition,
        array $images = [],
        array $ingredients = [],
        array $tags = [],
        array $prices = [],
    ): self {
        $now = new DateTimeImmutable();

        return new self(
            id: null,
            name: $name,
            description: $description,
            nutrition: $nutrition,
            images: $images,
            ingredients: $ingredients,
            tags: $tags,
            prices: $prices,
            status: self::STATUS_ACTIVE,
            createdAt: $now,
            updatedAt: $now,
            archivedAt: null,
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

    public function description(): string
    {
        return $this->description;
    }

    public function nutrition(): Nutrition
    {
        return $this->nutrition;
    }

    /**
     * @return ProductImage[]
     */
    public function images(): array
    {
        return $this->images;
    }

    /**
     * @return ProductIngredient[]
     */
    public function ingredients(): array
    {
        return $this->ingredients;
    }

    /**
     * @return ProductTag[]
     */
    public function tags(): array
    {
        return $this->tags;
    }

    /**
     * @return Price[]
     */
    public function prices(): array
    {
        return $this->prices;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function archivedAt(): ?DateTimeImmutable
    {
        return $this->archivedAt;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
        $this->touch();
    }

    public function changeDescription(string $description): void
    {
        $this->description = $description;
        $this->touch();
    }

    public function setNutrition(Nutrition $nutrition): void
    {
        $this->nutrition = $nutrition;
        $this->touch();
    }

    /**
     * @param ProductImage[] $images
     */
    public function setImages(array $images): void
    {
        $this->images = $images;
        $this->touch();
    }

    /**
     * @param ProductIngredient[] $ingredients
     */
    public function setIngredients(array $ingredients): void
    {
        $this->ingredients = $ingredients;
        $this->touch();
    }

    /**
     * @param ProductTag[] $tags
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
        $this->touch();
    }

    /**
     * @param Price[] $prices
     */
    public function setPrices(array $prices): void
    {
        $this->prices = $prices;
        $this->touch();
    }

    public function priceForStatus(\App\Domain\Product\VO\CustomerStatus $status): ?Price
    {
        $code = $status->code();

        foreach ($this->prices as $price) {
            if ($price->customerStatus()->code() === $code) {
                return $price;
            }
        }

        foreach ($this->prices as $price) {
            if ($price->isDefault()) {
                return $price;
            }
        }

        return null;
    }

    public function archive(): void
    {
        $this->status = self::STATUS_ARCHIVED;
        $this->archivedAt = new DateTimeImmutable();
        $this->touch();
    }

    public function activate(): void
    {
        $this->status = self::STATUS_ACTIVE;
        $this->archivedAt = null;
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}

