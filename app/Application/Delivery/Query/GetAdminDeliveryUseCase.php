<?php

namespace App\Application\Delivery\Query;

use App\Application\Delivery\Presenter\DeliveryPresenter;
use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;

/**
 * Админский срез доставки. Outside-zone surcharge в Domain нет — отдаём тот же снимок.
 */
final class GetAdminDeliveryUseCase
{
    public function __construct(
        private readonly DeliveryConfigurationRepository $delivery,
        private readonly DeliveryPresenter $presenter,
    ) {}

    /**
     * @return array{
     *     settings: array<string, mixed>,
     *     zones: list<array<string, mixed>>,
     *     zone_geojson: array<string, mixed>|null,
     *     kitchen_address: array<string, mixed>
     * }
     */
    public function execute(): array
    {
        $configuration = $this->delivery->findPublic();
        if ($configuration === null) {
            throw new \InvalidArgumentException('Настройки доставки не найдены.');
        }

        return $this->presenter->present($configuration);
    }
}
