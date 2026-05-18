<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Pages\ManageCatalog;
use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Товары';

    public function mount(): void
    {
        $this->redirect(
            ManageCatalog::getUrl(['tab' => 'products']),
            navigate: true
        );
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
