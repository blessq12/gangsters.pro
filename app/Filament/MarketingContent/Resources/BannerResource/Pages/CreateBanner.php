<?php

namespace App\Filament\MarketingContent\Resources\BannerResource\Pages;

use App\Filament\MarketingContent\Concerns\RedirectsToMarketingHub;
use App\Filament\MarketingContent\Resources\BannerResource;
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
