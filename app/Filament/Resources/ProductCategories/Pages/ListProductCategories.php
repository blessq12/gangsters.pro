<?php

namespace App\Filament\Resources\ProductCategories\Pages;

use App\Filament\Pages\ManageCatalog;
use App\Filament\Resources\ProductCategories\ProductCategoryResource;
use Filament\Resources\Pages\ListRecords;

class ListProductCategories extends ListRecords
{
    protected static string $resource = ProductCategoryResource::class;

    protected static ?string $title = 'Категории';

    public function mount(): void
    {
        $this->redirect(
            ManageCatalog::getUrl(['tab' => 'categories']),
            navigate: true
        );
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
