<?php

namespace App\Domain\Core\Order\Entity;

use App\Domain\Core\Order\Enum\ProductPromotionRoleKind;

/**
 * Роль товара в акциях + снимок name/price на момент назначения.
 */
final class ProductPromotionRole
{
    public function __construct(
        private readonly int $branchId,
        private readonly int $productId,
        private readonly ProductPromotionRoleKind $role,
        private readonly string $name,
        private readonly int $priceKopecks,
        private readonly ?int $paidTwinProductId = null,
    ) {
        if ($this->branchId < 1) {
            throw new \InvalidArgumentException('branch_id должен быть положительным.');
        }

        if ($this->productId < 1) {
            throw new \InvalidArgumentException('product_id должен быть положительным.');
        }

        if (trim($this->name) === '') {
            throw new \InvalidArgumentException('Нужно название снимка товара.');
        }

        if ($this->priceKopecks < 0) {
            throw new \InvalidArgumentException('Цена снимка не может быть отрицательной.');
        }
    }

    public function branchId(): int
    {
        return $this->branchId;
    }

    public function productId(): int
    {
        return $this->productId;
    }

    public function role(): ProductPromotionRoleKind
    {
        return $this->role;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function priceKopecks(): int
    {
        return $this->priceKopecks;
    }

    public function paidTwinProductId(): ?int
    {
        return $this->paidTwinProductId;
    }

    public function withSnapshot(string $name, int $priceKopecks): self
    {
        return new self(
            branchId: $this->branchId,
            productId: $this->productId,
            role: $this->role,
            name: $name,
            priceKopecks: $priceKopecks,
            paidTwinProductId: $this->paidTwinProductId,
        );
    }

    public function countsAsRoll(): bool
    {
        return $this->role === ProductPromotionRoleKind::Roll;
    }

    public function isComplementSet(): bool
    {
        return $this->role === ProductPromotionRoleKind::Complement;
    }

    public function isGiftCandidate(): bool
    {
        return $this->role === ProductPromotionRoleKind::Gift;
    }
}
