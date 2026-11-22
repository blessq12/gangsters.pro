<?php

namespace App\Application\Delivery\Command;

use App\Application\Delivery\DTO\SaveDeliveryConfigurationDto;
use App\Application\Delivery\Event\DeliveryZonesChangedFactory;
use App\Application\Delivery\Presenter\DeliveryPresenter;
use App\Domain\Core\Delivery\Tariff\Repository\DeliveryConfigurationRepository;
use App\Domain\Supporting\Company\Entity\Branch;
use App\Domain\Supporting\Company\Repository\BranchRepository;

final class SaveDeliveryConfigurationUseCase
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $delivery,
        private readonly BranchRepository $branches,
        private readonly DeliveryPresenter $presenter,
        private readonly DeliveryZonesChangedFactory $zonesChanged,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SaveDeliveryConfigurationDto $dto): array
    {
        if ($dto->minOrderRubles !== null && $dto->minOrderRubles < 0) {
            throw new \InvalidArgumentException('Минимальный заказ не может быть отрицательным.');
        }

        if ($dto->averageDeliveryTimeMinutes !== null && $dto->averageDeliveryTimeMinutes < 0) {
            throw new \InvalidArgumentException('Среднее время доставки не может быть отрицательным.');
        }

        $branchId = $dto->branchId;
        if ($branchId < 1) {
            throw new \InvalidArgumentException('Некорректный branch_id.');
        }

        $branch = $this->branches->findById($branchId);
        if (! $branch instanceof Branch) {
            throw new \InvalidArgumentException('Филиал не найден.');
        }

        $zones = [];
        foreach ($dto->zones as $zone) {
            if (! is_array($zone)) {
                continue;
            }

            $id = trim((string) ($zone['id'] ?? ''));
            $name = trim((string) ($zone['name'] ?? ''));
            if ($id === '' || $name === '') {
                throw new \InvalidArgumentException('У зоны нужны идентификатор и название.');
            }

            $fee = (int) ($zone['fee'] ?? $zone['fee_rubles'] ?? 0);
            if ($fee < 0) {
                throw new \InvalidArgumentException('Тариф зоны не может быть отрицательным.');
            }

            $zones[] = [
                'id' => $id,
                'name' => $name,
                'fee_rubles' => $fee,
                'is_remote' => (bool) ($zone['is_remote'] ?? false),
            ];
        }

        $zonesBefore = $this->zonesChanged->snapshot($this->delivery->findByBranch($branchId));

        $configuration = $this->delivery->saveSettingsForBranch(
            branchId: $branchId,
            minOrderAmountKopecks: $dto->minOrderRubles === null ? null : $dto->minOrderRubles * 100,
            averageDeliveryTimeMinutes: $dto->averageDeliveryTimeMinutes,
            zones: $zones,
        );

        $event = $this->zonesChanged->createIfChanged($zonesBefore, $configuration);
        if ($event !== null) {
            event($event);
        }

        return $this->presenter->present($configuration, $branch);
    }
}
