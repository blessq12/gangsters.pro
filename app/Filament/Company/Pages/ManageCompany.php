<?php

namespace App\Filament\Company\Pages;

use App\Domain\Admin\Enums\AdminHub;
use App\Filament\Company\Tables\HubDocumentsTable;
use App\Filament\Company\Tables\HubStaffTable;
use App\Filament\Company\Widgets\HubCompanyLegalPanel;
use App\Filament\Company\Widgets\HubCompanyProfilePanel;
use App\Filament\Company\Widgets\HubCompanySeoPanel;
use App\Filament\Support\Concerns\AuthorizesAdminHub;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageCompany extends Page
{
    use AuthorizesAdminHub;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Компания';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Компания';

    protected static ?string $slug = 'company';

    public function getHeading(): string
    {
        return 'Компания';
    }

    protected static function adminHub(): AdminHub
    {
        return AdminHub::Company;
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
                        Tab::make('SEO')
                            ->id('seo')
                            ->schema([
                                Livewire::make(HubCompanySeoPanel::class),
                            ]),
                    ]),
            ]);
    }
}
