<?php

namespace App\Filament\Content\MarketingContent\Resources\PromotionResource\Pages;

use App\Filament\Content\MarketingContent\Concerns\RedirectsToMarketingHub;
use App\Filament\Content\MarketingContent\Resources\PromotionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePromotion extends CreateRecord
{
    use RedirectsToMarketingHub;

    protected static string $resource = PromotionResource::class;

    protected static ?string $title = 'Новая акция';

    protected static function marketingHubTab(): string
    {
        return 'promotions';
    }
}
