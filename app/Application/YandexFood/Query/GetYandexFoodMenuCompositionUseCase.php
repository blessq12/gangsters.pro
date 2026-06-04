<?php

namespace App\Application\YandexFood\Query;

use App\Application\Integrations\Contracts\IntegrationMenuExportReadPort;
use App\Application\YandexFood\DTO\YandexMenuCompositionRequestDto;
use App\Application\YandexFood\Presenter\YandexFoodMenuCatalogPresenter;

final class GetYandexFoodMenuCompositionUseCase
{
    public function __construct(
        private readonly IntegrationMenuExportReadPort $menuExport,
        private readonly YandexFoodMenuCatalogPresenter $yandexMenuCatalog,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(YandexMenuCompositionRequestDto $dto): array
    {
        $blocks = $this->menuExport->getActiveMenuBlocks();

        return $this->yandexMenuCatalog->presentMenuComposition($blocks);
    }
}
