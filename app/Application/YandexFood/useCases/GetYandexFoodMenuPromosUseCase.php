<?php

namespace App\Application\YandexFood\useCases;

use App\Application\YandexFood\Presenter\YandexFoodMenuPresenter;

final class GetYandexFoodMenuPromosUseCase
{
    public function __construct(
        private readonly YandexFoodMenuPresenter $presenter,
    ) {}

    /**
     * @return array{promoItems: list<mixed>}
     */
    public function execute(): array
    {
        return $this->presenter->presentPromos();
    }
}
