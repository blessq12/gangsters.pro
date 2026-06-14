<?php

namespace App\Filament\Catalog\Resources\TagResource\Pages;

use App\Filament\Catalog\Concerns\CatalogContextBreadcrumbs;
use App\Filament\Catalog\Concerns\RedirectsToCatalogHub;
use App\Filament\Catalog\Resources\TagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTag extends CreateRecord
{
    use CatalogContextBreadcrumbs;
    use RedirectsToCatalogHub;

    protected static string $resource = TagResource::class;

    protected static ?string $title = 'Новый тег';

    protected static function catalogHubTab(): string
    {
        return 'tags';
    }
}
