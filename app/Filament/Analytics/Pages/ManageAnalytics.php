<?php

namespace App\Filament\Analytics\Pages;

use App\Application\Reporting\ValueObject\MetricsPeriod;
use App\Domain\Admin\Enums\AdminHub;
use App\Filament\Analytics\Widgets\Hub\HubClientsPanel;
use App\Filament\Analytics\Widgets\Hub\HubFinancePanel;
use App\Filament\Analytics\Widgets\Hub\HubOrdersPanel;
use App\Filament\Analytics\Widgets\Hub\HubOverviewPanel;
use App\Filament\Analytics\Widgets\Hub\HubStorefrontPanel;
use App\Filament\Catalog\Pages\ManageCatalog;
use App\Filament\Operations\Pages\ManageOperations;
use App\Filament\Support\Concerns\AuthorizesAdminHub;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Url;

class ManageAnalytics extends Page
{
    use AuthorizesAdminHub;

    protected static ?string $slug = 'dashboard';

    protected static ?string $navigationLabel = 'Главная';

    protected static ?string $title = 'Аналитика';

    protected static ?int $navigationSort = -2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    #[Url]
    public ?string $period = '7d';

    public function mount(): void
    {
        if ($this->period === null || $this->period === '') {
            $this->period = MetricsPeriod::SevenDays->value;
        }
    }

    protected static function adminHub(): AdminHub
    {
        return AdminHub::Analytics;
    }

    public function updatedPeriod(?string $period): void
    {
        if ($period === null || $period === '') {
            $this->period = MetricsPeriod::SevenDays->value;
        }

        try {
            MetricsPeriod::fromString((string) $this->period);
        } catch (\InvalidArgumentException) {
            $this->period = MetricsPeriod::SevenDays->value;
        }

        $this->redirect(static::getUrl([
            'period' => $this->period,
            'tab' => request()->query('tab', 'overview'),
        ]));
    }

    public function getHeading(): string
    {
        return 'Аналитика';
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        $tab = request()->query('tab', 'overview');

        return [
            Action::make('operations')
                ->label('Операции')
                ->icon(Heroicon::OutlinedCog6Tooth)
                ->url(ManageOperations::getUrl(['tab' => 'orders']))
                ->visible($tab === 'orders'),
            Action::make('catalog')
                ->label('Каталог')
                ->icon(Heroicon::OutlinedRectangleStack)
                ->url(ManageCatalog::getUrl(['tab' => 'products']))
                ->visible($tab === 'storefront'),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('period')
                            ->label('Период')
                            ->options(MetricsPeriod::options())
                            ->default($this->period ?? MetricsPeriod::SevenDays->value)
                            ->live(),
                    ])
                    ->compact(),
                Tabs::make('analyticsTabs')
                    ->persistTabInQueryString('tab')
                    ->tabs([
                        Tab::make('Обзор')
                            ->id('overview')
                            ->icon(Heroicon::OutlinedPresentationChartLine)
                            ->schema([
                                Livewire::make(HubOverviewPanel::class),
                            ]),
                        Tab::make('Финансы')
                            ->id('finance')
                            ->icon(Heroicon::OutlinedBanknotes)
                            ->schema([
                                Livewire::make(HubFinancePanel::class),
                            ]),
                        Tab::make('Клиенты')
                            ->id('clients')
                            ->icon(Heroicon::OutlinedUserGroup)
                            ->schema([
                                Livewire::make(HubClientsPanel::class),
                            ]),
                        Tab::make('Заказы')
                            ->id('orders')
                            ->icon(Heroicon::OutlinedShoppingCart)
                            ->schema([
                                Livewire::make(HubOrdersPanel::class),
                            ]),
                        Tab::make('Витрина')
                            ->id('storefront')
                            ->icon(Heroicon::OutlinedShoppingBag)
                            ->schema([
                                Livewire::make(HubStorefrontPanel::class),
                            ]),
                    ]),
            ]);
    }
}
