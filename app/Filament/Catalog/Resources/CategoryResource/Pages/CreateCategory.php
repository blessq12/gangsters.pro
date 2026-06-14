<?php

namespace App\Filament\Catalog\Resources\CategoryResource\Pages;

use App\Filament\Catalog\Concerns\CatalogContextBreadcrumbs;
use App\Filament\Catalog\Concerns\RedirectsToCatalogHub;
use App\Filament\Catalog\Resources\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    use CatalogContextBreadcrumbs;
    use RedirectsToCatalogHub;

    protected static string $resource = CategoryResource::class;

    protected static ?string $title = 'Новая категория';

    protected static function catalogHubTab(): string
    {
        return 'categories';
    }
}
