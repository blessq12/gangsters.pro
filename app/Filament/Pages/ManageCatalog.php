<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ProductCategories\ProductCategoryResource;
use App\Filament\Resources\ProductCategories\Tables\ProductCategoriesTable;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Filament\Resources\ProductTags\ProductTagResource;
use App\Filament\Resources\ProductTags\Tables\ProductTagsTable;
use App\Infrastructure\Category\Model\PRD_Category;
use App\Infrastructure\Product\Model\PRD_Product;
use App\Infrastructure\Product\Model\PRD_Tag;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use UnitEnum;

class ManageCatalog extends Page implements HasTable
{
    use InteractsWithTable {
        makeTable as makeBaseTable;
    }

    protected static string $routePath = 'catalog';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Каталог';

    protected static ?string $title = 'Каталог';

    protected static string|UnitEnum|null $navigationGroup = 'Каталог';

    protected static ?int $navigationSort = 20;

    #[Url(as: 'tab')]
    public string $catalogTab = 'products';

    public function mount(): void
    {
        $this->catalogTab = $this->normalizeCatalogTab($this->catalogTab);
    }

    public function setCatalogTab(string $tab): void
    {
        $tab = $this->normalizeCatalogTab($tab);

        if ($this->catalogTab === $tab) {
            return;
        }

        $this->catalogTab = $tab;
        $this->resetTable();
    }

    public function updatedCatalogTab(): void
    {
        $this->catalogTab = $this->normalizeCatalogTab($this->catalogTab);
        $this->resetTable();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(fn (): string => match ($this->catalogTab) {
                    'categories' => 'Создать категорию',
                    'tags' => 'Создать тег',
                    default => 'Создать товар',
                })
                ->url(fn (): string => $this->createUrlForActiveTab()),
        ];
    }

    public function table(Table $table): Table
    {
        return match ($this->catalogTab) {
            'categories' => ProductCategoriesTable::configure($table, ProductCategoryResource::class),
            'tags' => ProductTagsTable::configure($table, ProductTagResource::class),
            default => ProductsTable::configure($table, ProductResource::class),
        };
    }

    protected function makeTable(): Table
    {
        return $this->makeBaseTable()
            ->query(fn (): Builder => $this->getTableQuery());
    }

    protected function getTableQuery(): Builder
    {
        return match ($this->catalogTab) {
            'categories' => PRD_Category::query(),
            'tags' => PRD_Tag::query(),
            default => PRD_Product::query(),
        };
    }

    protected function getTableQueryStringIdentifier(): ?string
    {
        return 'catalog_'.$this->catalogTab;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('catalogTabs')
                    ->id('catalog')
                    ->columnSpanFull()
                    ->livewireProperty('catalogTab')
                    ->persistTabInQueryString('tab')
                    ->tabs([
                        'products' => Tab::make('Товары'),
                        'categories' => Tab::make('Категории'),
                        'tags' => Tab::make('Теги'),
                    ]),
                EmbeddedTable::make()
                    ->key(fn (): string => 'catalog-table-'.$this->catalogTab),
            ]);
    }

    private function createUrlForActiveTab(): string
    {
        return match ($this->catalogTab) {
            'categories' => ProductCategoryResource::getUrl('create'),
            'tags' => ProductTagResource::getUrl('create'),
            default => ProductResource::getUrl('create'),
        };
    }

    private function normalizeCatalogTab(string $tab): string
    {
        if (! in_array($tab, ['products', 'categories', 'tags'], true)) {
            return 'products';
        }

        return $tab;
    }
}
