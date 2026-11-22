<?php

namespace App\Application\Delivery\Query;

use App\Application\Delivery\Presenter\DeliveryPresenter;
use App\Domain\Supporting\Company\Repository\BranchRepository;
use App\Domain\Core\Delivery\Tariff\Repository\DeliveryConfigurationRepository;

/**
 * Публичный срез доставки для витрины.
 * Адрес филиала при необходимости читается из Company.
 */
final class GetPublicDeliveryUseCase
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

        return $this->presenter->present($configuration, $branch);
    }
}
