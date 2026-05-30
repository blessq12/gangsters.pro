<?php

namespace App\Filament\Catalog\Widgets;

use App\Application\Catalog\Query\GetAdminCatalogOverviewQuery;
use Filament\Widgets\Widget;

class CatalogOverviewWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.catalog.widgets.catalog-overview';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'categories' => app(GetAdminCatalogOverviewQuery::class)->execute()['categories'],
        ];
    }
}
