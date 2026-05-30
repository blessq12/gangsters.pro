<?php

namespace App\Filament\Catalog\Pages;

use App\Filament\Catalog\Tables\HubCategoriesTable;
use App\Filament\Catalog\Tables\HubLayoutTable;
use App\Filament\Catalog\Tables\HubProductsTable;
use App\Filament\Catalog\Tables\HubTagsTable;
use App\Filament\Catalog\Widgets\CatalogOverviewWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageCatalog extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Каталог';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Каталог';

    protected static ?string $slug = 'catalog';

    public function getHeading(): string
    {
        return 'Каталог';
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('catalogTabs')
                    ->persistTabInQueryString('tab')
                    ->tabs([
                        Tab::make('Обзор')
                            ->id('overview')
                            ->schema([
                                Livewire::make(CatalogOverviewWidget::class),
                            ]),
                        Tab::make('Товары')
                            ->id('products')
                            ->schema([
                                Livewire::make(HubProductsTable::class),
                            ]),
                        Tab::make('Категории')
                            ->id('categories')
                            ->schema([
                                Livewire::make(HubCategoriesTable::class),
                            ]),
                        Tab::make('Раскладка')
                            ->id('layout')
                            ->schema([
                                Livewire::make(HubLayoutTable::class),
                            ]),
                        Tab::make('Теги')
                            ->id('tags')
                            ->schema([
                                Livewire::make(HubTagsTable::class),
                            ]),
                    ]),
            ]);
    }
}
