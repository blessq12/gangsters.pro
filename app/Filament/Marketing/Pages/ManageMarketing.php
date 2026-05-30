<?php

namespace App\Filament\Marketing\Pages;

use App\Filament\Marketing\Tables\HubBannersTable;
use App\Filament\Marketing\Tables\HubPromotionsTable;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageMarketing extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $navigationLabel = 'Маркетинг';

    protected static ?int $navigationSort = 6;

    protected static ?string $title = 'Маркетинг';

    protected static ?string $slug = 'marketing';

    public function getHeading(): string
    {
        return 'Маркетинг';
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('marketingTabs')
                    ->persistTabInQueryString('tab')
                    ->tabs([
                        Tab::make('Баннеры')
                            ->id('banners')
                            ->schema([
                                Livewire::make(HubBannersTable::class),
                            ]),
                        Tab::make('Акции')
                            ->id('promotions')
                            ->schema([
                                Livewire::make(HubPromotionsTable::class),
                            ]),
                    ]),
            ]);
    }
}
