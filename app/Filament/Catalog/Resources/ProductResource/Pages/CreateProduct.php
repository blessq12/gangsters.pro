<?php

namespace App\Filament\Catalog\Resources\ProductResource\Pages;

use App\Domain\Catalog\Enum\CatalogItemKind;
use App\Filament\Catalog\Concerns\CatalogContextBreadcrumbs;
use App\Filament\Catalog\Resources\ProductResource;
use App\Filament\Catalog\Support\FilamentProductPersistence;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use CatalogContextBreadcrumbs;

    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Новый товар';

    protected static function catalogHubTab(): string
    {
        return 'products';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', [
            'record' => $this->getRecord(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['catalog_kind'] = CatalogItemKind::Product->value;

        return FilamentProductPersistence::normalize($data);
    }
}
