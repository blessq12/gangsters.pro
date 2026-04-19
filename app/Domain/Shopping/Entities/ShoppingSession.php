<?php

namespace App\Domain\Shopping\Entities;

final class ShoppingSession
{
    /**
     * @param  CartLine[]  $cartLines
     * @param  int[]  $favoriteProductIds
     * @param  array<string, mixed>|null  $checkoutDraft
     */
    public function __construct(
        private int $id,
        private string $publicId,
        private ?int $clientId,
        private \DateTimeImmutable $expiresAt,
        private array $cartLines,
        private array $favoriteProductIds,
        private ?array $checkoutDraft,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /**
     * @return CartLine[]
     */
    public function getCartLines(): array
    {
        return $this->cartLines;
    }

    /**
     * @return int[]
     */
    public function getFavoriteProductIds(): array
    {
        return $this->favoriteProductIds;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCheckoutDraft(): ?array
    {
        return $this->checkoutDraft;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setClientId(?int $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    /**
     * @param  array<string, mixed>|null  $draft
     */
    public function setCheckoutDraft(?array $draft): void
    {
        $this->checkoutDraft = $draft;
    }

    public function upsertCartLine(int $productId, int $quantity, ?array $payload = null): void
    {
        if ($quantity < 1) {
            $this->removeCartLine($productId);

            return;
        }

        foreach ($this->cartLines as $i => $line) {
            if ($line->productId === $productId) {
                $this->cartLines[$i] = new CartLine($productId, $quantity, $payload);

                return;
            }
        }

        $this->cartLines[] = new CartLine($productId, $quantity, $payload);
    }

    public function removeCartLine(int $productId): void
    {
        $this->cartLines = array_values(array_filter(
            $this->cartLines,
            static fn (CartLine $line) => $line->productId !== $productId,
        ));
    }

    public function clearCart(): void
    {
        $this->cartLines = [];
    }

    public function addFavorite(int $productId): void
    {
        if (! in_array($productId, $this->favoriteProductIds, true)) {
            $this->favoriteProductIds[] = $productId;
        }
    }

    public function removeFavorite(int $productId): void
    {
        $this->favoriteProductIds = array_values(array_filter(
            $this->favoriteProductIds,
            static fn (int $id) => $id !== $productId,
        ));
    }

    public function hasFavorite(int $productId): bool
    {
        return in_array($productId, $this->favoriteProductIds, true);
    }

    /**
     * @return array<int, array{product_id: int, quantity: int}>
     */
    public function cartLinesAsOrderItems(): array
    {
        return array_map(
            static fn (CartLine $line) => $line->toOrderItemRow(),
            $this->cartLines,
        );
    }

    public function isEmptyCart(): bool
    {
        return $this->cartLines === [];
    }
}
