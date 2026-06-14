<?php

namespace App\Filament\MarketingContent\Resources\BannerResource\Pages;

use App\Filament\MarketingContent\Concerns\RedirectsToMarketingHub;
use App\Filament\MarketingContent\Resources\BannerResource;
use Filament\Resources\Pages\EditRecord;

class EditBanner extends EditRecord
{
    use RedirectsToMarketingHub;

    protected static string $resource = BannerResource::class;

    protected static ?string $title = 'Редактирование баннера';

    protected static function marketingHubTab(): string
    {
        return 'banners';
    }
}
