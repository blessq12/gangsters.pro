<?php

namespace App\Filament\Pages;

use App\Application\Reporting\ValueObject\MetricsPeriod;
use App\Filament\Widgets\Business\ChannelStats;
use App\Filament\Widgets\Business\ClientsKpiStats;
use App\Filament\Widgets\Business\DeliveryMixChart;
use App\Filament\Widgets\Business\OrdersCountChart;
use App\Filament\Widgets\Business\OrdersPipelineStats;
use App\Filament\Widgets\Business\OverviewKpiStats;
use App\Filament\Widgets\Business\PaymentMixChart;
use App\Filament\Widgets\Business\RecentOrdersTable;
use App\Filament\Widgets\Business\RevenueTrendChart;
use App\Filament\Widgets\Business\ShoppingFunnelStats;
use App\Filament\Widgets\Business\TopClientsTable;
use App\Filament\Widgets\Business\TopProductsTable;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Livewire\Attributes\Url;

class BusinessDashboard extends BaseDashboard
{
    use HasFiltersAction;

    protected static ?string $navigationLabel = 'Дашборд';

    protected static ?string $title = 'Показатели';

    protected static ?int $navigationSort = -2;

    #[Url(as: 'tab')]
    public string $metricsTab = 'overview';

    public function mount(): void
    {
        $this->metricsTab = $this->normalizeMetricsTab($this->metricsTab);
        $this->mountHasFilters();

        if (! isset($this->filters['period']) || $this->filters['period'] === '') {
            $this->filters['period'] = MetricsPeriod::SevenDays->value;
        }
    }

    public function updatedMetricsTab(): void
    {
        $this->metricsTab = $this->normalizeMetricsTab($this->metricsTab);
    }

    /**
     * @return array<class-string>
     */
    public function getWidgets(): array
    {
        return match ($this->metricsTab) {
            'orders' => [
                OrdersPipelineStats::class,
                DeliveryMixChart::class,
                PaymentMixChart::class,
                RecentOrdersTable::class,
            ],
            'clients' => [
                ClientsKpiStats::class,
                TopClientsTable::class,
                TopProductsTable::class,
            ],
            'funnel' => [
                ShoppingFunnelStats::class,
                ChannelStats::class,
            ],
            default => [
                OverviewKpiStats::class,
                RevenueTrendChart::class,
                OrdersCountChart::class,
            ],
        };
    }

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->schema([
                    Select::make('period')
                        ->label('Период')
                        ->options(MetricsPeriod::options())
                        ->required()
                        ->native(false),
                ]),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('metricsTabs')
                    ->id('metrics')
                    ->columnSpanFull()
                    ->livewireProperty('metricsTab')
                    ->persistTabInQueryString('tab')
                    ->tabs([
                        'overview' => Tab::make('Сводка'),
                        'orders' => Tab::make('Заказы'),
                        'clients' => Tab::make('Клиенты и товары'),
                        'funnel' => Tab::make('Воронка'),
                    ]),
                $this->getWidgetsContentComponent()
                    ->key(fn (): string => 'metrics-widgets-'.$this->metricsTab),
            ]);
    }

    /**
     * @return int|array<string, int|null>
     */
    public function getColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 2,
        ];
    }

    private function normalizeMetricsTab(string $tab): string
    {
        if (! in_array($tab, ['overview', 'orders', 'clients', 'funnel'], true)) {
            return 'overview';
        }

        return $tab;
    }
}
