<?php

namespace App\Application\Branch\Query;

use App\Application\Branch\Presenter\DeliveryPresenter;
use App\Domain\Supporting\Branch\Repository\BranchRepository;
use App\Domain\Supporting\Branch\Repository\DeliveryConfigurationRepository;

/**
 * Админский срез доставки. Адрес филиала — через порт своего BC.
 */
final class GetAdminDeliveryUseCase
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $delivery,
        private readonly BranchRepository $branches,
        private readonly DeliveryPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $branchId): array
    {
        $configuration = $this->delivery->findByBranch($branchId);
        if ($configuration === null) {
            throw new \InvalidArgumentException('Настройки доставки филиала не найдены.');
        }

        $branch = $configuration->branchId() !== null
            ? $this->branches->findById($configuration->branchId())
            : null;

        return $this->presenter->present(
            $configuration,
            $this->presenter->addressFromBranch($branch),
        );
    }
}
