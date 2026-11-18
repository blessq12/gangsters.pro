<?php

namespace App\Application\Delivery\Query;

use App\Application\Delivery\Presenter\DeliveryPresenter;
use App\Domain\Company\Repository\KitchenRepository;
use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;

/**
 * Админский срез доставки.
 * Адрес кухни при необходимости читается из Company.
 */
final class GetAdminDeliveryUseCase
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $delivery,
        private readonly KitchenRepository $kitchens,
        private readonly DeliveryPresenter $presenter,
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

        $kitchen = $configuration->kitchenId() !== null
            ? $this->kitchens->findById($configuration->kitchenId())
            : null;

        return $this->presenter->present($configuration, $kitchen);
    }
}
