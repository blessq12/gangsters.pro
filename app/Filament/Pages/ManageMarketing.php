<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Banners\BannerResource;
use App\Filament\Resources\Banners\Tables\BannersTable;
use App\Filament\Resources\Promotions\PromotionResource;
use App\Filament\Resources\Promotions\Tables\PromotionsTable;
use App\Infrastructure\SystemContent\Model\SYS_Banner;
use App\Infrastructure\SystemContent\Model\SYS_Promotion;
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

class ManageMarketing extends Page implements HasTable
{
    use InteractsWithTable {
        makeTable as makeBaseTable;
    }

    protected static string $routePath = 'marketing';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $navigationLabel = 'Маркетинг';

    protected static ?string $title = 'Маркетинг';

    protected static string|UnitEnum|null $navigationGroup = 'Маркетинг';

    protected static ?int $navigationSort = 40;

    #[Url(as: 'tab')]
    public string $marketingTab = 'banners';

    public function mount(): void
    {
        $this->marketingTab = $this->normalizeMarketingTab($this->marketingTab);
    }

    public function setMarketingTab(string $tab): void
    {
        $tab = $this->normalizeMarketingTab($tab);

        if ($this->marketingTab === $tab) {
            return;
        }

        $this->marketingTab = $tab;
        $this->resetTable();
    }

    public function updatedMarketingTab(): void
    {
        $this->marketingTab = $this->normalizeMarketingTab($this->marketingTab);
        $this->resetTable();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(fn (): string => match ($this->marketingTab) {
                    'promotions' => 'Создать акцию',
                    default => 'Создать баннер',
                })
                ->url(fn (): string => $this->createUrlForActiveTab()),
        ];
    }

    public function table(Table $table): Table
    {
        return match ($this->marketingTab) {
            'promotions' => PromotionsTable::configure($table, PromotionResource::class),
            default => BannersTable::configure($table, BannerResource::class),
        };
    }

    protected function makeTable(): Table
    {
        return $this->makeBaseTable()
            ->query(fn (): Builder => $this->getTableQuery());
    }

    protected function getTableQuery(): Builder
    {
        return match ($this->marketingTab) {
            'promotions' => SYS_Promotion::query(),
            default => SYS_Banner::query(),
        };
    }

    protected function getTableQueryStringIdentifier(): ?string
    {
        return 'marketing_'.$this->marketingTab;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('marketingTabs')
                    ->id('marketing')
                    ->columnSpanFull()
                    ->livewireProperty('marketingTab')
                    ->persistTabInQueryString('tab')
                    ->tabs([
                        'banners' => Tab::make('Баннеры'),
                        'promotions' => Tab::make('Акции'),
                    ]),
                EmbeddedTable::make()
                    ->key(fn (): string => 'marketing-table-'.$this->marketingTab),
            ]);
    }

    private function createUrlForActiveTab(): string
    {
        return match ($this->marketingTab) {
            'promotions' => PromotionResource::getUrl('create'),
            default => BannerResource::getUrl('create'),
        };
    }

    private function normalizeMarketingTab(string $tab): string
    {
        if (! in_array($tab, ['banners', 'promotions'], true)) {
            return 'banners';
        }

        return $tab;
    }
}
