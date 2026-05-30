<?php

namespace App\Filament\Marketing\Pages;

use App\Domain\Admin\Enums\AdminHub;
use App\Filament\Marketing\Tables\HubBannersTable;
use App\Filament\Marketing\Tables\HubPromotionsTable;
use App\Filament\Support\Concerns\AuthorizesAdminHub;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageMarketing extends Page
{
    use AuthorizesAdminHub;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $navigationLabel = 'Маркетинг';

    protected static ?int $navigationSort = 6;

    protected static ?string $title = 'Маркетинг';

    protected static ?string $slug = 'marketing';

    public function getHeading(): string
    {
        return 'Маркетинг';
    }

    protected static function adminHub(): AdminHub
    {
        return AdminHub::Marketing;
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
