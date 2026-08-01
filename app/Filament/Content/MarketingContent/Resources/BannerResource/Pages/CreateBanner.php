<?php

namespace App\Filament\Content\MarketingContent\Resources\BannerResource\Pages;

use App\Filament\Content\MarketingContent\Concerns\RedirectsToMarketingHub;
use App\Filament\Content\MarketingContent\Resources\BannerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBanner extends CreateRecord
{
    use RedirectsToMarketingHub;

    protected static string $resource = BannerResource::class;

    protected static ?string $title = 'Новый баннер';

    protected static function marketingHubTab(): string
    {
        return 'banners';
    }
}
