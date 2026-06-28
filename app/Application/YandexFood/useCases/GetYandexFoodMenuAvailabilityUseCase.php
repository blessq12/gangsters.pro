<?php

namespace App\Application\YandexFood\useCases;

use App\Application\YandexFood\Port\YandexFoodMenuCatalogPort;
use App\Application\YandexFood\Presenter\YandexFoodMenuPresenter;

final class GetYandexFoodMenuAvailabilityUseCase
{
    public function __construct(
        private readonly YandexFoodMenuCatalogPort $catalogReader,
        private readonly YandexFoodMenuPresenter $presenter,
    ) {}

    /**
     * @return array{items: list<array{id: string, quantity: int}>, modifiers: list<mixed>}
     */
    public function execute(): array
    {
        return $this->presenter->presentAvailability(
            $this->catalogReader->readUnavailablePartnerSkus(),
        );
    }
}
