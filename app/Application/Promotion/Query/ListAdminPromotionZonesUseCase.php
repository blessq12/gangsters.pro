<?php

namespace App\Application\Promotion\Query;

use App\Application\Promotion\Presenter\PromotionPolicyPresenter;
use App\Application\Shared\PageParams;
use App\Domain\Core\Delivery\Tariff\Entity\PromotionZone;
use App\Domain\Core\Delivery\Tariff\Repository\PromotionZoneRepository;

final class ListAdminPromotionZonesUseCase
{
    public function __construct(
        private readonly PromotionZoneRepository $zones,
        private readonly PromotionPolicyPresenter $presenter,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(int $branchId, int $offset = 0, int $limit = PageParams::DEFAULT_LIMIT): array
    {
        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);

        return PageParams::wrap(
            array_map(
                fn (PromotionZone $zone): array => $this->presenter->zone($zone),
                $this->zones->listPageByBranch($branchId, $offset, $limit),
            ),
            $this->zones->countByBranch($branchId),
            $offset,
            $limit,
        );
    }
}
