<?php

namespace App\Filament\MarketingContent\Resources\PromotionResource\Pages;

use App\Filament\MarketingContent\Concerns\RedirectsToMarketingHub;
use App\Filament\MarketingContent\Resources\PromotionResource;
use Filament\Resources\Pages\EditRecord;

class EditPromotion extends EditRecord
{
    use RedirectsToMarketingHub;

    protected static string $resource = PromotionResource::class;

    protected static ?string $title = 'Редактирование акции';

    protected static function marketingHubTab(): string
    {
        return 'promotions';
    }
}
