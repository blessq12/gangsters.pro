<?php

namespace App\Filament\Catalog\Resources\TagResource\Pages;

use App\Filament\Catalog\Concerns\CatalogContextBreadcrumbs;
use App\Filament\Catalog\Concerns\RedirectsToCatalogHub;
use App\Filament\Catalog\Resources\TagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTag extends EditRecord
{
    use CatalogContextBreadcrumbs;
    use RedirectsToCatalogHub;

    protected static string $resource = TagResource::class;

    protected static ?string $title = 'Редактирование тега';

    protected static function catalogHubTab(): string
    {
        return 'tags';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
