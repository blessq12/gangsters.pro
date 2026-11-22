<?php

namespace App\Application\Promotion\Command;

use App\Domain\Core\Order\Enum\ProductPromotionRoleKind;
use App\Domain\Core\Order\Repository\ProductPromotionRoleRepository;

final class DeleteProductPromotionRoleUseCase
{
    public function __construct(
        private readonly ProductPromotionRoleRepository $roles,
    ) {}

    public function execute(int $branchId, int $productId, string $role): void
    {
        $kind = ProductPromotionRoleKind::tryFrom(trim($role));
        if ($kind === null) {
            throw new \InvalidArgumentException('Роль должна быть roll, gift или complement.');
        }

        if ($branchId < 1) {
            throw new \InvalidArgumentException('branch_id должен быть положительным.');
        }

        if ($productId < 1) {
            throw new \InvalidArgumentException('product_id должен быть положительным.');
        }

        $this->roles->delete($branchId, $productId, $kind);
    }
}
