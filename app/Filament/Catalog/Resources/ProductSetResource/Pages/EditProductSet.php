<?php

namespace App\Filament\Catalog\Resources\ProductSetResource\Pages;

use App\Filament\Catalog\Concerns\CatalogContextBreadcrumbs;
use App\Filament\Catalog\Concerns\RedirectsToCatalogHub;
use App\Filament\Catalog\Concerns\RendersCatalogResourceTabs;
use App\Filament\Catalog\Resources\ProductSetResource;
use App\Filament\Catalog\Resources\ProductResource\RelationManagers\ProductImagesRelationManager;
use App\Filament\Catalog\Resources\ProductSetResource\RelationManagers\ProductSetLinesRelationManager;
use App\Filament\Catalog\Resources\ProductSetResource\Schemas\ProductSetForm;
use App\Filament\Catalog\Support\FilamentProductPersistence;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class EditProductSet extends EditRecord
{
    use CatalogContextBreadcrumbs;
    use RedirectsToCatalogHub;
    use RendersCatalogResourceTabs;

    protected static string $resource = ProductSetResource::class;

    protected static ?string $title = 'Редактирование набора';

    protected static function catalogHubTab(): string
    {
        return 'sets';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->catalogEditTabs(
                    Tabs::make('set-context')
                    ->tabs([
                        'card' => Tab::make('card')
                            ->label('Карточка')
                            ->icon(Heroicon::OutlinedSquares2x2)
                            ->columns(2)
                            ->schema(ProductSetForm::cardTabSchema()),
                        'composition' => Tab::make('composition')
                            ->label('Состав')
                            ->icon(Heroicon::OutlinedListBullet)
                            ->schema([
                                $this->relationManagerTab(ProductSetLinesRelationManager::class),
                            ]),
                        'images' => Tab::make('images')
                            ->label('Изображения')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->schema([
                                $this->relationManagerTab(ProductImagesRelationManager::class),
                            ]),
                    ]),
                ),
            ]);
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->ensureDefaultCatalogEditTab('card', ['card', 'composition', 'images']);
        FilamentProductPersistence::ensureSetKind($this->getRecord());
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return FilamentProductPersistence::normalize($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
