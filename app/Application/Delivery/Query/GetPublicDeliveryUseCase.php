<?php

namespace App\Application\Delivery\Query;

use App\Application\Delivery\Presenter\DeliveryPresenter;
use App\Domain\Supporting\Company\Repository\KitchenRepository;
use App\Domain\Core\Delivery\Tariff\Repository\DeliveryConfigurationRepository;

/**
 * Публичный срез доставки для витрины.
 * Адрес кухни при необходимости читается из Company.
 */
final class GetPublicDeliveryUseCase
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
