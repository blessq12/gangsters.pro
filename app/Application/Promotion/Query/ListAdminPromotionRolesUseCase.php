<?php

namespace App\Application\Promotion\Query;

use App\Application\Promotion\Presenter\PromotionPolicyPresenter;
use App\Application\Shared\PageParams;
use App\Domain\Core\Order\Entity\ProductPromotionRole;
use App\Domain\Core\Order\Repository\ProductPromotionRoleRepository;

final class ListAdminPromotionRolesUseCase
{
    public function __construct(
        private readonly ProductPromotionRoleRepository $roles,
        private readonly PromotionPolicyPresenter $presenter,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(int $offset = 0, int $limit = PageParams::DEFAULT_LIMIT): array
    {
        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);

        return PageParams::wrap(
            array_map(
                fn (ProductPromotionRole $role): array => $this->presenter->role($role),
                $this->roles->listPage($offset, $limit),
            ),
            $this->roles->countAll(),
            $offset,
            $limit,
        );
    }
}
