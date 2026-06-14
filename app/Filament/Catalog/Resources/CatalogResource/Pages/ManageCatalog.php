<?php

namespace App\Filament\Catalog\Resources\CatalogResource\Pages;

use App\Filament\Catalog\Resources\CatalogResource;
use App\Filament\Catalog\Widgets\Tables\CategoriesHubTable;
use App\Filament\Catalog\Widgets\Tables\ProductSetsHubTable;
use App\Filament\Catalog\Widgets\Tables\ProductsHubTable;
use App\Filament\Catalog\Widgets\Tables\TagsHubTable;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Url;

class ManageCatalog extends Page
{
    protected static string $resource = CatalogResource::class;

    protected static ?string $title = 'Каталог';

    protected static ?string $navigationLabel = 'Каталог';

    /** @var list<string> */
    private const VALID_TABS = [
        'categories',
        'products',
        'sets',
        'tags',
    ];

    /**
     * Старые значения ?tab= от persistTabInQueryString (slug подписи + ::tab).
     *
     * @var array<string, string>
     */
    private const LEGACY_TAB_QUERY_VALUES = [
        '0' => 'categories',
        '1' => 'products',
        '2' => 'sets',
        '3' => 'tags',
        'kategorii::tab' => 'categories',
        'categories::tab' => 'categories',
        'tovary::tab' => 'products',
        'products::tab' => 'products',
        'nabory::tab' => 'sets',
        'sets::tab' => 'sets',
        'tegi::tab' => 'tags',
        'tags::tab' => 'tags',
    ];

    #[Url(as: 'tab', history: true)]
    public ?string $activeCatalogTab = 'categories';

    public function mount(): void
    {
        $this->ensureDefaultCatalogTab();
    }

    public function booted(): void
    {
        $this->ensureDefaultCatalogTab();
    }

    protected function ensureDefaultCatalogTab(): void
    {
        $candidate = $this->activeCatalogTab;

        $tabFromQuery = request()->query('tab');

        if (is_string($tabFromQuery) && $tabFromQuery !== '') {
            $candidate = $tabFromQuery;
        }

        if (isset(self::LEGACY_TAB_QUERY_VALUES[$candidate])) {
            $candidate = self::LEGACY_TAB_QUERY_VALUES[$candidate];
        }

        if (! in_array($candidate, self::VALID_TABS, true)) {
            $candidate = 'categories';
        }

        $this->activeCatalogTab = $candidate;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('catalog-context')
                    ->tabs([
                        'categories' => Tab::make('categories')
                            ->label('Категории')
                            ->icon(Heroicon::OutlinedFolder)
                            ->schema([
                                Livewire::make(CategoriesHubTable::class)
                                    ->key('catalog-hub-categories-table'),
                            ]),
                        'products' => Tab::make('products')
                            ->label('Товары')
                            ->icon(Heroicon::OutlinedCube)
                            ->schema([
                                Livewire::make(ProductsHubTable::class)
                                    ->key('catalog-hub-products-table'),
                            ]),
                        'sets' => Tab::make('sets')
                            ->label('Наборы')
                            ->icon(Heroicon::OutlinedSquares2x2)
                            ->schema([
                                Livewire::make(ProductSetsHubTable::class)
                                    ->key('catalog-hub-sets-table'),
                            ]),
                        'tags' => Tab::make('tags')
                            ->label('Теги')
                            ->icon(Heroicon::OutlinedTag)
                            ->schema([
                                Livewire::make(TagsHubTable::class)
                                    ->key('catalog-hub-tags-table'),
                            ]),
                    ])
                    ->livewireProperty('activeCatalogTab'),
            ]);
    }
}
