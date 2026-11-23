<?php

namespace App\Application\Delivery\Query;

use App\Application\Delivery\Presenter\DeliveryPresenter;
use App\Application\Shared\PageParams;
use App\Domain\Core\Delivery\Tariff\Repository\DeliveryConfigurationRepository;
use App\Domain\Core\Delivery\Tariff\ValueObject\DeliveryZone;

final class ListAdminDeliveryZonesUseCase
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $delivery,
        private readonly DeliveryPresenter $presenter,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(int $branchId, int $offset = 0, int $limit = PageParams::DEFAULT_LIMIT): array
    {
        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);

        return PageParams::wrap(
            array_map(
                fn (DeliveryZone $zone): array => $this->presenter->zone($zone),
                $this->delivery->listZonesPageByBranch($branchId, $offset, $limit),
            ),
            $this->delivery->countZonesByBranch($branchId),
            $offset,
            $limit,
        );
    }
}
