<?php

namespace App\Application\Promotion\Command;

use App\Application\Promotion\DTO\AssignProductPromotionRoleDto;
use App\Application\Promotion\Presenter\PromotionPolicyPresenter;
use App\Domain\Core\Order\Entity\ProductPromotionRole;
use App\Domain\Core\Order\Enum\ProductPromotionRoleKind;
use App\Domain\Core\Order\Repository\ProductPromotionRoleRepository;

final class AssignProductPromotionRoleUseCase
{
    public function __construct(
        private readonly ProductPromotionRoleRepository $roles,
        private readonly PromotionPolicyPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(AssignProductPromotionRoleDto $dto): array
    {
        $roleKind = ProductPromotionRoleKind::tryFrom(trim($dto->role));
        if ($roleKind === null) {
            throw new \InvalidArgumentException('Роль должна быть roll, gift или complement.');
        }

        $name = trim($dto->name);
        if ($name === '') {
            throw new \InvalidArgumentException('Нужно название снимка товара.');
        }

        if ($dto->productId < 1) {
            throw new \InvalidArgumentException('product_id должен быть положительным.');
        }

        if ($dto->priceKopecks < 0) {
            throw new \InvalidArgumentException('price_kopecks не может быть отрицательным.');
        }

        $saved = $this->roles->save(new ProductPromotionRole(
            productId: $dto->productId,
            role: $roleKind,
            name: $name,
            priceKopecks: $dto->priceKopecks,
            paidTwinProductId: $dto->paidTwinProductId,
        ));

        return $this->presenter->role($saved);
    }
}
