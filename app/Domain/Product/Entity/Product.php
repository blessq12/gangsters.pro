<?php

namespace App\Domain\Product\Entity;

use App\Domain\Product\VO\Nutrition;
use App\Domain\Product\VO\ProductTag;
use DateTimeImmutable;

final class Product
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * @param  ProductImage[]  $images
     * @param  ProductIngredient[]  $ingredients
     * @param  ProductTag[]  $tags
     */
    private function __construct(
        private ?int $id,
        private string $name,
        private ?string $articul,
        private string $description,
        private Nutrition $nutrition,
        private array $images,
        private array $ingredients,
        private array $tags,
        private bool $cartRuleCountsAsRoll,
        private bool $cartRuleGiftCandidate,
        private bool $cartRuleIsComplementSet,
        /** Цена в копейках (RUB), null — нет цены */
        private ?int $price,
        private string $status,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
        private ?DateTimeImmutable $archivedAt,
    ) {}

    /**
     * @param  ProductImage[]  $images
     * @param  ProductIngredient[]  $ingredients
     * @param  ProductTag[]  $tags
     */
    public static function create(
        string $name,
        string $description,
        Nutrition $nutrition,
        array $images = [],
        array $ingredients = [],
        array $tags = [],
        ?int $price = null, // копейки (RUB)
        ?string $articul = null,
    ): self {
        $now = new DateTimeImmutable;

        return new self(
            id: null,
            name: $name,
            articul: $articul,
            description: $description,
            nutrition: $nutrition,
            images: $images,
            ingredients: $ingredients,
            tags: $tags,
            cartRuleCountsAsRoll: false,
            cartRuleGiftCandidate: false,
            cartRuleIsComplementSet: false,
            price: $price,
            status: self::STATUS_ACTIVE,
            createdAt: $now,
            updatedAt: $now,
            archivedAt: null,
        );
    }

    /**
     * @param  ProductImage[]  $images
     * @param  ProductIngredient[]  $ingredients
     * @param  ProductTag[]  $tags
     */
    public static function reconstitute(
        int $id,
        string $name,
        ?string $articul,
        string $description,
        Nutrition $nutrition,
        array $images,
        array $ingredients,
        array $tags,
        bool $cartRuleCountsAsRoll,
        bool $cartRuleGiftCandidate,
        bool $cartRuleIsComplementSet,
        ?int $price,
        string $status,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
        ?DateTimeImmutable $archivedAt,
    ): self {
        return new self(
            id: $id,
            name: $name,
            articul: $articul,
            description: $description,
            nutrition: $nutrition,
            images: $images,
            ingredients: $ingredients,
            tags: $tags,
            cartRuleCountsAsRoll: $cartRuleCountsAsRoll,
            cartRuleGiftCandidate: $cartRuleGiftCandidate,
            cartRuleIsComplementSet: $cartRuleIsComplementSet,
            price: $price,
            status: $status,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            archivedAt: $archivedAt,
        );
    }

    public function assignPersistedId(int $id): void
    {
        if ($this->id === null) {
            $this->id = $id;
        }
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function articul(): ?string
    {
        return $this->articul;
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
     * Учитывается в правиле «комплект к роллам» (кол-во единиц для расчёта комплекта).
     * Не путать с тегами витрины для фильтрации.
     */
    public function cartRuleCountsAsRoll(): bool
    {
        return $this->cartRuleCountsAsRoll;
    }

    /**
     * Товар может быть выбран как бесплатный подарок при достижении порога корзины.
     */
    public function cartRuleGiftCandidate(): bool
    {
        return $this->cartRuleGiftCandidate;
    }

    /**
     * Этот товар — системная позиция «комплект» (соус/имбирь и т.д.), id подставляется правилом из каталога.
     */
    public function cartRuleIsComplementSet(): bool
    {
        return $this->cartRuleIsComplementSet;
    }

    public function setCartRuleCountsAsRoll(bool $value): void
    {
        $this->cartRuleCountsAsRoll = $value;
        $this->touch();
    }

    public function setCartRuleGiftCandidate(bool $value): void
    {
        $this->cartRuleGiftCandidate = $value;
        $this->touch();
    }

    public function setCartRuleIsComplementSet(bool $value): void
    {
        $this->cartRuleIsComplementSet = $value;
        $this->touch();
    }

    /** Цена в копейках (RUB), null — нет цены */
    public function price(): ?int
    {
        return $this->price;
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

    public function setArticul(?string $articul): void
    {
        $this->articul = $articul;
        $this->touch();
    }

    public function setNutrition(Nutrition $nutrition): void
    {
        $this->nutrition = $nutrition;
        $this->touch();
    }

    /**
     * @param  ProductImage[]  $images
     */
    public function setImages(array $images): void
    {
        $this->images = $images;
        $this->touch();
    }

    /**
     * @param  ProductIngredient[]  $ingredients
     */
    public function setIngredients(array $ingredients): void
    {
        $this->ingredients = $ingredients;
        $this->touch();
    }

    /**
     * @param  ProductTag[]  $tags
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
        $this->touch();
    }

    /** @param  ?int  $price  Копейки (RUB) */
    public function setPrice(?int $price): void
    {
        $this->price = $price;
        $this->touch();
    }

    public function archive(): void
    {
        $this->status = self::STATUS_ARCHIVED;
        $this->archivedAt = new DateTimeImmutable;
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
        $this->updatedAt = new DateTimeImmutable;
    }
}
