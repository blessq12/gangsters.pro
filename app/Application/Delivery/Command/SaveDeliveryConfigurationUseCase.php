<?php

namespace App\Application\Delivery\Command;

use App\Application\Delivery\DTO\SaveDeliveryConfigurationDto;
use App\Application\Delivery\Presenter\DeliveryPresenter;
use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;

final class SaveDeliveryConfigurationUseCase
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $delivery,
        private readonly DeliveryPresenter $presenter,
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

        $kitchen = $dto->kitchen;
        $configuration = $this->delivery->saveSettings(
            minOrderAmountKopecks: $dto->minOrderRubles === null ? null : $dto->minOrderRubles * 100,
            averageDeliveryTimeMinutes: $dto->averageDeliveryTimeMinutes,
            city: $this->nullable($kitchen['city'] ?? null),
            street: $this->nullable($kitchen['street'] ?? null),
            house: $this->nullable($kitchen['house'] ?? null),
            comment: $this->nullable($kitchen['comment'] ?? null),
            searchLine: $this->nullable($kitchen['search_line'] ?? null),
            zones: $zones,
        );

        return $this->presenter->present($configuration);
    }

    private function nullable(mixed $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed !== '' ? $trimmed : null;
    }
}
