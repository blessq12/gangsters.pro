<?php

namespace App\Filament\Catalog\Resources\ProductSetResource\Pages;

use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Filament\Catalog\Concerns\CatalogContextBreadcrumbs;
use App\Filament\Catalog\Concerns\RedirectsToCatalogHub;
use App\Filament\Catalog\Resources\ProductSetResource;
use App\Filament\Catalog\Support\FilamentProductPersistence;
use Filament\Resources\Pages\CreateRecord;

class CreateProductSet extends CreateRecord
{
    use CatalogContextBreadcrumbs;
    use RedirectsToCatalogHub;

    protected static string $resource = ProductSetResource::class;

    protected static ?string $title = 'Новый набор';

    protected static function catalogHubTab(): string
    {
        return 'sets';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['catalog_kind'] = CatalogItemKind::Set->value;

        return FilamentProductPersistence::normalize($data);
    }
}
