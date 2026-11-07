<?php

namespace App\Application\Content\Query;

use App\Domain\Content\Repository\DeliveryConfigurationRepository;

final class GetDeliverySettingsUseCase
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $delivery,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $configuration = $this->delivery->findPublic();
        if ($configuration === null) {
            throw new \InvalidArgumentException('Настройки доставки не найдены.');
        }

        $zones = [];
        foreach ($configuration->zones() as $zone) {
            $zones[] = [
                'id' => $zone->id(),
                'name' => $zone->name(),
                'fee_rubles' => intdiv($zone->deliveryFeeKopecks(), 100),
                'is_remote' => $zone->isRemote(),
            ];
        }

        $kitchen = $configuration->kitchenAddress();

        return [
            'min_order_rubles' => $configuration->minOrderAmountKopecks() === null
                ? null
                : intdiv($configuration->minOrderAmountKopecks(), 100),
            'average_delivery_time_minutes' => $configuration->averageDeliveryTimeMinutes(),
            'kitchen' => [
                'city' => $kitchen->city() ?? '',
                'street' => $kitchen->street() ?? '',
                'house' => $kitchen->house() ?? '',
                'comment' => $kitchen->comment() ?? '',
                'search_line' => $kitchen->searchLine() ?? '',
            ],
            'zones' => $zones,
        ];
    }
}
