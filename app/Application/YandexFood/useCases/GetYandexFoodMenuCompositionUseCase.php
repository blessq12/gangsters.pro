<?php

namespace App\Application\YandexFood\useCases;

use App\Application\YandexFood\Port\YandexFoodMenuCatalogPort;
use App\Application\YandexFood\Presenter\YandexFoodMenuPresenter;
use Illuminate\Support\Carbon;

final class GetYandexFoodMenuCompositionUseCase
{
    public function __construct(
        private readonly YandexFoodMenuCatalogPort $catalogReader,
        private readonly YandexFoodMenuPresenter $presenter,
    ) {}

    /**
     * @return array{categories: list<array<string, mixed>>, items: list<array<string, mixed>>, lastChange: string}
     */
    public function execute(): array
    {
        $catalog = $this->catalogReader->readCompositionCatalog();

        return $this->presenter->presentComposition(
            categories: $catalog['categories'],
            products: $catalog['products'],
            changedAt: Carbon::now(),
        );
    }
}
