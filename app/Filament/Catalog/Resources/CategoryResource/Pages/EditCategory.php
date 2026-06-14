<?php

namespace App\Filament\Catalog\Resources\CategoryResource\Pages;

use App\Filament\Catalog\Concerns\CatalogContextBreadcrumbs;
use App\Filament\Catalog\Concerns\RedirectsToCatalogHub;
use App\Filament\Catalog\Concerns\RendersCatalogResourceTabs;
use App\Filament\Catalog\Resources\CategoryResource;
use App\Filament\Catalog\Resources\CategoryResource\RelationManagers\CategoryProductsRelationManager;
use App\Filament\Catalog\Resources\CategoryResource\Schemas\CategoryForm;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class EditCategory extends EditRecord
{
    use CatalogContextBreadcrumbs;
    use RedirectsToCatalogHub;
    use RendersCatalogResourceTabs;

    protected static string $resource = CategoryResource::class;

    protected static ?string $title = 'Редактирование категории';

    protected static function catalogHubTab(): string
    {
        return 'categories';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->catalogEditTabs(
                    Tabs::make('category-context')
                    ->tabs([
                        'category' => Tab::make('category')
                            ->label('Категория')
                            ->icon(Heroicon::OutlinedFolder)
                            ->columns(2)
                            ->schema(CategoryForm::tabSchema()),
                        'composition' => Tab::make('composition')
                            ->label('Состав')
                            ->icon(Heroicon::OutlinedRectangleStack)
                            ->schema([
                                $this->relationManagerTab(CategoryProductsRelationManager::class),
                            ]),
                    ]),
                ),
            ]);
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->ensureDefaultCatalogEditTab('category', ['category', 'composition']);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
