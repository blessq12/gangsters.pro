<?php

namespace App\Infrastructure\Order\Port;

use App\Domain\Delivery\Entity\DeliveryConfiguration;
use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Domain\Delivery\ValueObject\DeliveryZone;
use App\Domain\Order\Port\OrderDeliveryPort;
use App\Shared\Geo\DeliveryZoneMatcher;

/**
 * Order → Delivery: зона, город кухни и минимальная сумма заказа из конфигурации доставки.
 */
final class OrderDeliveryAdapter implements OrderDeliveryPort
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $deliveryConfigurations,
    ) {}

    public function resolveZone(?float $latitude, ?float $longitude): ?DeliveryZone
    {
        $configuration = $this->deliveryConfigurations->findPublic();

        if (! $configuration instanceof DeliveryConfiguration) {
            return null;
        }

        return DeliveryZoneMatcher::match($configuration->zones(), $latitude, $longitude);
    }

    public function resolveKitchenCity(): ?string
    {
        return $this->deliveryConfigurations->findPublic()?->kitchenAddress()->city();
    }

    public function resolveMinOrderAmountKopecks(): ?int
    {
        return $this->deliveryConfigurations->findPublic()?->minOrderAmountKopecks();
    }
}
