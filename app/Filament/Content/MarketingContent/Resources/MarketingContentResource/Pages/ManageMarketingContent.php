<?php

namespace App\Filament\Content\MarketingContent\Resources\MarketingContentResource\Pages;

use App\Filament\Content\MarketingContent\Resources\MarketingContentResource;
use App\Filament\Content\MarketingContent\Widgets\Tables\BannersHubTable;
use App\Filament\Content\MarketingContent\Widgets\Tables\PromotionsHubTable;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Url;

class ManageMarketingContent extends Page
{
    protected static string $resource = MarketingContentResource::class;

    protected static ?string $title = 'Маркетинг';

    protected static ?string $navigationLabel = 'Маркетинг';

    /** @var list<string> */
    private const VALID_TABS = [
        'banners',
        'promotions',
    ];

    #[Url(as: 'tab', history: true)]
    public ?string $activeMarketingTab = 'banners';

    public function mount(): void
    {
        $this->ensureDefaultTab();
    }

    public function booted(): void
    {
        $this->ensureDefaultTab();
    }

    protected function ensureDefaultTab(): void
    {
        $candidate = $this->activeMarketingTab;
        $tabFromQuery = request()->query('tab');

        if (is_string($tabFromQuery) && $tabFromQuery !== '') {
            $candidate = $tabFromQuery;
        }

        if (! in_array($candidate, self::VALID_TABS, true)) {
            $candidate = 'banners';
        }

        $this->activeMarketingTab = $candidate;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('marketing-context')
                    ->tabs([
                        'banners' => Tab::make('banners')
                            ->label('Баннеры')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->schema([
                                Livewire::make(BannersHubTable::class)
                                    ->key('marketing-hub-banners-table'),
                            ]),
                        'promotions' => Tab::make('promotions')
                            ->label('Акции')
                            ->icon(Heroicon::OutlinedSparkles)
                            ->schema([
                                Livewire::make(PromotionsHubTable::class)
                                    ->key('marketing-hub-promotions-table'),
                            ]),
                    ])
                    ->livewireProperty('activeMarketingTab'),
            ]);
    }
}
