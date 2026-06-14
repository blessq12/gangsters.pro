<?php

namespace App\Filament\Catalog\Resources\ProductResource\Pages;

use App\Filament\Catalog\Concerns\CatalogContextBreadcrumbs;
use App\Filament\Catalog\Concerns\RedirectsToCatalogHub;
use App\Filament\Catalog\Concerns\RendersCatalogResourceTabs;
use App\Filament\Catalog\Resources\ProductResource;
use App\Filament\Catalog\Resources\ProductResource\RelationManagers\ProductImagesRelationManager;
use App\Filament\Catalog\Resources\ProductResource\Schemas\ProductForm;
use App\Filament\Catalog\Support\FilamentProductPersistence;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class EditProduct extends EditRecord
{
    use CatalogContextBreadcrumbs;
    use RedirectsToCatalogHub;
    use RendersCatalogResourceTabs;

    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Редактирование товара';

    protected static function catalogHubTab(): string
    {
        return 'products';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->catalogEditTabs(
                    Tabs::make('product-context')
                    ->tabs([
                        'card' => Tab::make('card')
                            ->label('Карточка')
                            ->icon(Heroicon::OutlinedCube)
                            ->columns(2)
                            ->schema([
                                ...ProductForm::cardTabSchema(),
                                ...ProductForm::tagsTabSchema(),
                                ...ProductForm::ingredientsTabSchema(),
                            ]),
                        'nutrition' => Tab::make('nutrition')
                            ->label('Пищевая ценность')
                            ->icon(Heroicon::OutlinedFire)
                            ->columns(2)
                            ->schema(ProductForm::nutritionTabSchema()),
                        'meta' => Tab::make('meta')
                            ->label('Мета товара')
                            ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                            ->columns(3)
                            ->schema(ProductForm::metaTabSchema()),
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

        $this->ensureDefaultCatalogEditTab('card', ['card', 'nutrition', 'meta', 'images']);
        FilamentProductPersistence::ensureProductKind($this->getRecord());
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
