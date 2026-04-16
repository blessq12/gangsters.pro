<?php

namespace App\Application\YandexFood\Query;

use App\Application\Catalog\Contracts\CatalogYandexReadModelContract;
use App\Application\YandexFood\DTO\YandexMenuCompositionRequestDto;
use App\Application\YandexFood\Presenter\YandexFoodMenuCatalogPresenter;
use App\Application\YandexFood\YandexFoodBaseUseCase;

final class GetYandexFoodMenuCompositionUseCase extends YandexFoodBaseUseCase
{
    public function __construct(
        private readonly CatalogYandexReadModelContract $catalogReadModel,
        private readonly YandexFoodMenuCatalogPresenter $yandexMenuCatalog,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(YandexMenuCompositionRequestDto $dto): array
    {
        $blocks = $this->catalogReadModel->getActiveMenuBlocks();

        return $this->yandexMenuCatalog->presentMenuComposition($blocks);
    }
}
