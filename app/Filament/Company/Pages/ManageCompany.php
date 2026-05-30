<?php

namespace App\Filament\Company\Pages;

use App\Filament\Company\Tables\HubDocumentsTable;
use App\Filament\Company\Tables\HubStaffTable;
use App\Filament\Company\Widgets\HubCompanyLegalPanel;
use App\Filament\Company\Widgets\HubCompanyProfilePanel;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageCompany extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Компания';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Компания';

    protected static ?string $slug = 'company';

    public function getHeading(): string
    {
        return 'Компания';
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('companyTabs')
                    ->persistTabInQueryString('tab')
                    ->tabs([
                        Tab::make('Профиль')
                            ->id('profile')
                            ->schema([
                                Livewire::make(HubCompanyProfilePanel::class),
                            ]),
                        Tab::make('Юрлицо')
                            ->id('legal')
                            ->schema([
                                Livewire::make(HubCompanyLegalPanel::class),
                            ]),
                        Tab::make('Документы')
                            ->id('documents')
                            ->schema([
                                Livewire::make(HubDocumentsTable::class),
                            ]),
                        Tab::make('Сотрудники')
                            ->id('staff')
                            ->schema([
                                Livewire::make(HubStaffTable::class),
                            ]),
                    ]),
            ]);
    }
}
