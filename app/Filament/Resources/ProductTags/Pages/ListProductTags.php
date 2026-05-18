<?php

namespace App\Filament\Resources\ProductTags\Pages;

use App\Filament\Pages\ManageCatalog;
use App\Filament\Resources\ProductTags\ProductTagResource;
use Filament\Resources\Pages\ListRecords;

class ListProductTags extends ListRecords
{
    protected static string $resource = ProductTagResource::class;

    protected static ?string $title = 'Теги товаров';

    public function mount(): void
    {
        $this->redirect(
            ManageCatalog::getUrl(['tab' => 'tags']),
            navigate: true
        );
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
