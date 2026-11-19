<?php

namespace App\Application\Delivery\Command;

use App\Application\Delivery\DTO\SaveDeliveryConfigurationDto;
use App\Application\Delivery\Event\DeliveryZonesChangedFactory;
use App\Application\Delivery\Presenter\DeliveryPresenter;
use App\Domain\Supporting\Company\Entity\Kitchen;
use App\Domain\Supporting\Company\Repository\KitchenRepository;
use App\Domain\Core\Delivery\Tariff\Repository\DeliveryConfigurationRepository;

final class SaveDeliveryConfigurationUseCase
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $delivery,
        private readonly KitchenRepository $kitchens,
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

        $kitchenId = $dto->kitchenId;
        $kitchen = null;
        if ($kitchenId !== null) {
            if ($kitchenId < 1) {
                throw new \InvalidArgumentException('Некорректный kitchen_id.');
            }
            $kitchen = $this->kitchens->findById($kitchenId);
            if (! $kitchen instanceof Kitchen) {
                throw new \InvalidArgumentException('Кухня не найдена.');
            }
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

        $zonesBefore = $this->zonesChanged->snapshot($this->delivery->findPublic());

        $configuration = $this->delivery->saveSettings(
            minOrderAmountKopecks: $dto->minOrderRubles === null ? null : $dto->minOrderRubles * 100,
            averageDeliveryTimeMinutes: $dto->averageDeliveryTimeMinutes,
            kitchenId: $kitchenId,
            zones: $zones,
        );

        $event = $this->zonesChanged->createIfChanged($zonesBefore, $configuration);
        if ($event !== null) {
            event($event);
        }

        return $this->presenter->present($configuration, $kitchen);
    }
}
